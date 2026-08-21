<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class CSVReader
{
    private $handle;
    private $separator = ',';
    private $enclosure = '"';
    private $escape = '\\';
    private $max_row_size = 4096;


    function __construct($params = array()) {
        if (count($params) > 0) {
            $this->initialize($params);
        }
    }


    function initialize($params = array()) {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }


    /**
     * Read entire CSV file and normalize to UTF-8 (UTF-8 BOM, UTF-16 Excel exports, legacy encodings).
     *
     * @param string $filepath
     * @return string|false
     */
    private function normalizeFileToUtf8($filepath)
    {
        $raw = file_get_contents($filepath);
        if ($raw === false) {
            return false;
        }

        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
            $raw = substr($raw, 3);
        } elseif (substr($raw, 0, 2) === "\xFF\xFE") {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (substr($raw, 0, 2) === "\xFE\xFF") {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (strpos($raw, "\x00") !== false) {
            // Excel "CSV UTF-8" on Windows is often UTF-16LE without BOM (null bytes between chars).
            $converted = @mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                $raw = $converted;
            }
        } elseif (!mb_check_encoding($raw, 'UTF-8')) {
            $detected = mb_detect_encoding($raw, array('UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'), true);
            if ($detected && strtoupper($detected) !== 'UTF-8') {
                $converted = @mb_convert_encoding($raw, 'UTF-8', $detected);
                if ($converted !== false) {
                    $raw = $converted;
                }
            }
        }

        return $raw;
    }


    /**
     * Ensure a single CSV cell is valid UTF-8 without stripping Devanagari / Nepali text.
     *
     * @param string $cell
     * @return string
     */
    private function normalizeCellUtf8($cell)
    {
        $cell = trim((string) $cell);
        if ($cell === '') {
            return '';
        }
        if (strpos($cell, "\x00") !== false) {
            $converted = @mb_convert_encoding($cell, 'UTF-8', 'UTF-16LE');
            if ($converted !== false) {
                $cell = trim($converted);
            }
        }
        if (mb_check_encoding($cell, 'UTF-8')) {
            return $cell;
        }
        foreach (array('UTF-16LE', 'UTF-16BE', 'UTF-8', 'ISO-8859-1', 'Windows-1252') as $enc) {
            $converted = @mb_convert_encoding($cell, 'UTF-8', $enc);
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                return trim($converted);
            }
        }

        return $cell;
    }


    public function parse_file($filepath)
    {
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return FALSE;
        }

        $utf8 = $this->normalizeFileToUtf8($filepath);
        if ($utf8 === false) {
            return FALSE;
        }

        $this->handle = fopen('php://memory', 'r+');
        if ($this->handle === FALSE) {
            return FALSE;
        }
        fwrite($this->handle, $utf8);
        rewind($this->handle);

        // Auto-detect delimiter using the first non-empty line
        $start_position = ftell($this->handle);
        $probing_line = '';
        while (($probing_line = fgets($this->handle)) !== false) {
            if (trim($probing_line) !== '') {
                $detected = $this->detectDelimiter($probing_line);
                if (!empty($detected)) {
                    $this->separator = $detected;
                    if (function_exists('log_message')) {
                        log_message('debug', 'CSVReader detected delimiter: ' . $this->separator);
                    }
                }
                break;
            }
        }
        fseek($this->handle, $start_position);

        $header = NULL;
        $data = array();

        while (($row = fgetcsv($this->handle, $this->max_row_size, $this->separator, $this->enclosure, $this->escape)) !== FALSE) {
            if (empty(array_filter($row))) {
                continue;
            }

            foreach ($row as &$cell) {
                $cell = $this->normalizeCellUtf8($cell);
            }
            unset($cell);

            if (!$header) {
                $header = $row;
                $header = array_map('strtolower', $header);
                $header[0] = str_replace("\xEF\xBB\xBF", '', $header[0]);
                continue;
            }

            if (count($row) != count($header)) {
                continue;
            }

            $row_data = array_combine($header, $row);

            if (!empty(array_filter($row_data))) {
                $data[] = $row_data;
            }
        }

        fclose($this->handle);
        $this->handle = null;

        return $data;
    }

    private function detectDelimiter($line)
    {
        $candidates = array(
            ','  => substr_count($line, ','),
            ';'  => substr_count($line, ';'),
            "\t" => substr_count($line, "\t"),
            '|'  => substr_count($line, '|'),
        );

        arsort($candidates);
        $top = array_key_first($candidates);

        if ($candidates[$top] === 0) {
            return $this->separator;
        }

        return $top;
    }


    public function fgetcsvUTF8($handle, $length = 4096, $separator = ';')
    {
        $handler = fopen($handle, "r");
        if (($buffer = fgets($handler, $length)) !== false) {
            $buffer = $this->autoUTF($buffer);
            $keys   = str_getcsv('Name');
            while (($row = fgetcsv($handler, 2, $separator, '"')) != false) {                
                if ($row != null) {
                    // skip empty lines
                    $values = str_getcsv($row[0]);
                    if (count($keys) == count($values)) {
                        $arr = array();
                        for ($j = 0; $j < count($keys); $j++) {
                            if ($keys[$j] != "") {
                                if ($keys[$j] == "admission_date(dd-mm-yyyy)" || $keys[$j] == "dob(dd-mm-yyyy)") {
                                    $search        = "(dd-mm-yyyy)";
                                    $trimmed       = str_replace($search, '', $keys[$j]);
                                    $arr[$trimmed] = date('Y-m-d', strtotime($values[$j]));
                                } else {
                                    $arr[$keys[$j]] = $values[$j];
                                }
                            }
                        }
                        $content[$i] = $arr;
                        $i++;
                    }
                }
            }
            return $content;
        }
        return false;
    }


    /**
     * automatic convertion windows-1250 and iso-8859-2 info utf-8 string
     *
     * @param   string  $s
     *
     * @return  string
     */
    private function autoUTF($s)
    {
        // detect UTF-8
        if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $s)) {
            return $s;
        }


        // detect WINDOWS-1250
        if (preg_match('#[\x7F-\x9F\xBC]#', $s)) {
            return iconv('WINDOWS-1250', 'UTF-8', $s);
        }


        // assume ISO-8859-2
        return iconv('ISO-8859-2', 'UTF-8', $s);
    }
}
