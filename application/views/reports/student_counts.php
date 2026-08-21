<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> Student Count Reports
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Student Count Summary</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <div class="download_label">Student Count Reports</div>
                           
                            <!-- Faculty and Gender Counts -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#facultyGenderCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Faculty and Gender Distribution</a>
                                    </h4>
                                </div>
                                <div id="facultyGenderCollapse" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Faculty</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($faculty_gender_counts) && !empty($faculty_gender_counts)) {
                                                    foreach ($faculty_gender_counts as $count) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $count['faculty']; ?></td>
                                                            <td><?php echo $count['male']; ?></td>
                                                            <td><?php echo $count['female']; ?></td>
                                                            <td><?php echo $count['total']; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                         <!-- Faculty and Ethnicity -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#facultyEthnicityCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Faculty and Ethnicity Distribution</a>
                                </h4>
                            </div>
                            <div id="facultyEthnicityCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr style="background-color: #f5f5f5;">
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: bold;">Faculty</th>
                                                    <th colspan="<?php echo count($ethnicitylist); ?>" class="text-center" style="font-weight: bold; border-bottom: 2px solid #ddd;">Ethnicity Distribution</th>
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: bold;">Total</th>
                                                </tr>
                                                <tr style="background-color: #f9f9f9;">
                                                    <?php foreach ($ethnicitylist as $ethnicity): ?>
                                                        <th class="text-center" style="font-weight: bold;"><?php echo $ethnicity['name']; ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($faculty_ethnicity_structured) && !empty($faculty_ethnicity_structured)) {
                                                    $row_counter = 0;
                                                    foreach ($faculty_ethnicity_structured as $faculty_name => $ethnicities) {
                                                        $row_class = ($row_counter % 2 == 0) ? '' : 'active';
                                                        ?>
                                                        <tr class="<?php echo $row_class; ?>">
                                                            <td style="font-weight: bold; padding-left: 15px;">
                                                                <?php echo htmlspecialchars($faculty_name); ?>
                                                            </td>
                                                            <?php 
                                                            foreach ($ethnicitylist as $ethnicity): 
                                                                $count = isset($ethnicities[$ethnicity['id']]) ? $ethnicities[$ethnicity['id']] : 0;
                                                                $cell_class = '';
                                                                if ($count > 0) {
                                                                    if ($count >= 50) $cell_class = 'success';
                                                                    elseif ($count >= 20) $cell_class = 'warning';
                                                                    elseif ($count >= 10) $cell_class = 'info';
                                                                }
                                                            ?>
                                                                <td class="text-center <?php echo $cell_class; ?>" style="font-weight: 500;">
                                                                    <?php echo $count; ?>
                                                                </td>
                                                            <?php endforeach; ?>
                                                            <td class="text-center" style="font-weight: bold; background-color: #f0f0f0;">
                                                                <?php echo isset($ethnicities['Total']) ? $ethnicities['Total'] : 0; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $row_counter++;
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="<?php echo count($ethnicitylist) + 2; ?>" class="text-center" style="padding: 30px; color: #999;">
                                                            <i class="fa fa-info-circle" style="margin-right: 8px;"></i>
                                                            No data available for Faculty and Ethnicity distribution
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="info" style="background-color: #d9edf7; font-weight: bold;">
                                                    <th style="text-align: center; font-size: 14px;">
                                                        <i class="fa fa-calculator" style="margin-right: 8px;"></i>
                                                        Grand Total
                                                    </th>
                                                    <?php
                                                    $grand_totals = [];
                                                    foreach ($ethnicitylist as $ethnicity) {
                                                        $grand_totals[$ethnicity['id']] = 0;
                                                    }
                                                    $overall_total = 0;
                                                    
                                                    if (isset($faculty_ethnicity_structured) && !empty($faculty_ethnicity_structured)) {
                                                        foreach ($faculty_ethnicity_structured as $faculty_name => $ethnicities) {
                                                            foreach ($ethnicitylist as $ethnicity) {
                                                                $grand_totals[$ethnicity['id']] += isset($ethnicities[$ethnicity['id']]) ? $ethnicities[$ethnicity['id']] : 0;
                                                            }
                                                            $overall_total += isset($ethnicities['Total']) ? $ethnicities['Total'] : 0;
                                                        }
                                                    }
                                                    
                                                    foreach ($ethnicitylist as $ethnicity):
                                                    ?>
                                                        <th class="text-center" style="font-size: 14px;">
                                                            <?php echo $grand_totals[$ethnicity['id']]; ?>
                                                        </th>
                                                    <?php endforeach; ?>
                                                    <th class="text-center" style="font-size: 16px; background-color: #337ab7; color: white;">
                                                        <?php echo $overall_total; ?>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                            <!-- Level and Gender Counts -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#levelGenderCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Level and Gender Distribution</a>
                                    </h4>
                                </div>
                                <div id="levelGenderCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($level_gender_counts) && !empty($level_gender_counts)) {
                                                    foreach ($level_gender_counts as $count) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $count['level']; ?></td>
                                                            <td><?php echo $count['male']; ?></td>
                                                            <td><?php echo $count['female']; ?></td>
                                                            <td><?php echo $count['total']; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Program and Gender Counts -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#programGenderCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Program and Gender Distribution</a>
                                    </h4>
                                </div>
                                <div id="programGenderCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Program</th>
                                                    <th>Male</th>
                                                    <th>Female</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (isset($program_gender_counts) && !empty($program_gender_counts)) {
                                                    foreach ($program_gender_counts as $count) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $count['program']; ?></td>
                                                            <td><?php echo $count['male']; ?></td>
                                                            <td><?php echo $count['female']; ?></td>
                                                            <td><?php echo $count['total']; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                          <!-- Program Ethnicity Gender Counts -->
                          <?php
                            $ethnicities = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];

                            $grand_total = [
                                'total' => 0,
                                'ethnicities' => []
                            ];

                            // Initialize grand totals for each ethnicity
                            foreach ($ethnicities as $ethnicity) {
                                $grand_total['ethnicities'][$ethnicity] = ['male' => 0, 'female' => 0];
                            }
                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#programEthnicityCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Program Ethnicity Gender Distribution</a>
                                    </h4>
                                </div>
                                <div id="programEthnicityCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-6">
                                                <input type="text" class="form-control input-sm" id="programEthnicitySearch" placeholder="Search..." style="height: 30px;">
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" id="programEthnicityCopyBtn">Copy</button>
                                                    <button class="btn btn-default" id="programEthnicityCsvBtn">CSV</button>
                                                    <button class="btn btn-default" id="programEthnicityExcelBtn">Excel</button>
                                                    <button class="btn btn-default" id="programEthnicityPdfBtn">PDF</button>
                                                    <button class="btn btn-default" id="programEthnicityPrintBtn">Print</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="programEthnicityTable" style="font-size: 12px;">
                                                <thead>
                                                    <tr style="background-color: #5bc0de; color: white;">
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 40px;">S.No.</th>
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 200px;">Program Name</th>
                                                        <?php foreach ($ethnicities as $ethnicity): ?>
                                                            <th colspan="2" style="text-align: center;"><?php echo $ethnicity; ?></th>
                                                        <?php endforeach; ?>
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; ">Total</th>
                                                    </tr>
                                                    <tr>
                                                        <?php foreach ($ethnicities as $ethnicity): ?>
                                                            <th style="text-align: center;">Male</th>
                                                            <th style="text-align: center;">Female</th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($program_ethnicity_counts) && !empty($program_ethnicity_counts)): ?>
                                                        <?php $serial = 1; ?>
                                                        <?php foreach ($program_ethnicity_counts as $program_name => $data): ?>
                                                            <tr>
                                                                <td style="text-align: center;"><?php echo $serial++; ?></td>
                                                                <td><?php echo htmlspecialchars($program_name); ?></td>

                                                                <?php foreach ($ethnicities as $ethnicity): ?>
                                                                    <?php
                                                                        $male = $data['ethnicities'][$ethnicity]['male'] ?? 0;
                                                                        $female = $data['ethnicities'][$ethnicity]['female'] ?? 0;

                                                                        // Accumulate grand totals
                                                                        $grand_total['ethnicities'][$ethnicity]['male'] += $male;
                                                                        $grand_total['ethnicities'][$ethnicity]['female'] += $female;
                                                                    ?>
                                                                    <td style="text-align: center;"><?php echo $male; ?></td>
                                                                    <td style="text-align: center;"><?php echo $female; ?></td>
                                                                <?php endforeach; ?>

                                                                <?php
                                                                    $program_total = $data['gender']['total'] ?? 0;
                                                                    $grand_total['total'] += $program_total;
                                                                ?>
                                                                <td style="text-align: center; font-weight: bold; background-color: #f9f9f9;"><?php echo $program_total; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>

                                                        <!-- Grand Total Row -->
                                                        <tr style="background-color: #f0f0f0; font-weight: bold;">
                                                            <td colspan="2" style="text-align: center;">Grand Total</td>
                                                            <?php foreach ($ethnicities as $ethnicity): ?>
                                                                <td style="text-align: center;"><?php echo $grand_total['ethnicities'][$ethnicity]['male']; ?></td>
                                                                <td style="text-align: center;"><?php echo $grand_total['ethnicities'][$ethnicity]['female']; ?></td>
                                                            <?php endforeach; ?>
                                                            <td style="text-align: center; background-color: #d9edf7;"><?php echo $grand_total['total']; ?></td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="21" style="text-align: center; padding: 20px;">No data available</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- District, Ethnicity and Gender Distribution -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#districtEthnicityCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">District, Ethnicity and Gender Distribution</a>
                                    </h4>
                                </div>
                                <div id="districtEthnicityCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-6">
                                                <input type="text" class="form-control input-sm" id="districtEthnicitySearch" placeholder="Search..." style="height: 30px;">
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" id="districtEthnicityCopyBtn">Copy</button>
                                                    <button class="btn btn-default" id="districtEthnicityCsvBtn">CSV</button>
                                                    <button class="btn btn-default" id="districtEthnicityExcelBtn">Excel</button>
                                                    <button class="btn btn-default" id="districtEthnicityPdfBtn">PDF</button>
                                                    <button class="btn btn-default" id="districtEthnicityPrintBtn">Print</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Table with the same class structure as other tables -->
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="districtEthnicityTable">
                                                <thead>
                                                    <tr style="background-color: #f5f5f5;">
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; font-weight: bold;">District</th>
                                                        <?php foreach ($ethnicitylist as $ethnicity): ?>
                                                            <th colspan="2" class="text-center" style="font-weight: bold;"><?php echo $ethnicity['name']; ?></th>
                                                        <?php endforeach; ?>
                                                        <th class="text-center" style="font-weight: bold;">Total</th>
                                                    </tr>
                                                    <tr style="background-color: #f9f9f9;">
                                                        <?php foreach ($ethnicitylist as $ethnicity): ?>
                                                            <th class="text-center" style="font-weight: bold;">M</th>
                                                            <th class="text-center" style="font-weight: bold;">F</th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (!empty($district_ethnicity_structured)) {
                                                        foreach ($district_ethnicity_structured as $district_name => $ethnicities) {
                                                            echo "<tr class='district-row'>";
                                                            echo "<td class='district-name' style='font-weight: bold;'>".htmlspecialchars($district_name)."</td>";

                                                            foreach ($ethnicitylist as $ethnicity) {
                                                                $m = $ethnicities[$ethnicity['id']]['Male'] ?? 0;
                                                                $f = $ethnicities[$ethnicity['id']]['Female'] ?? 0;

                                                                echo "<td class='text-center'>".$m."</td>";
                                                                echo "<td class='text-center'>".$f."</td>";
                                                            }

                                                            $total = ($ethnicities['Total']['Male'] ?? 0) + ($ethnicities['Total']['Female'] ?? 0);
                                                            echo "<td class='text-center' style='font-weight: bold;'>".$total."</td>";

                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='".((count($ethnicitylist) * 2) + 2)."' class='text-center' style='padding: 30px; color: #999;'>No data available</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>

                                                <tfoot>
                                                    <tr style="background-color: #d9edf7; font-weight: bold;">
                                                        <th>Grand Total</th>
                                                        <?php
                                                        $grand_totals = [];
                                                        foreach ($ethnicitylist as $ethnicity) {
                                                            $grand_totals[$ethnicity['id']] = ['Male' => 0, 'Female' => 0];
                                                        }
                                                        $maleTotal = $femaleTotal = 0;

                                                        foreach ($district_ethnicity_structured as $district => $eths) {
                                                            foreach ($ethnicitylist as $ethnicity) {
                                                                $grand_totals[$ethnicity['id']]['Male'] += $eths[$ethnicity['id']]['Male'] ?? 0;
                                                                $grand_totals[$ethnicity['id']]['Female'] += $eths[$ethnicity['id']]['Female'] ?? 0;
                                                            }
                                                            $maleTotal += $eths['Total']['Male'] ?? 0;
                                                            $femaleTotal += $eths['Total']['Female'] ?? 0;
                                                        }

                                                        foreach ($ethnicitylist as $ethnicity) {
                                                            $gm = $grand_totals[$ethnicity['id']]['Male'];
                                                            $gf = $grand_totals[$ethnicity['id']]['Female'];
                                                            echo "<th class='text-center'>".($gm != 0 ? $gm : '<span style="color: #000; font-weight: bold;">0</span>')."</th>";
                                                            echo "<th class='text-center'>".($gf != 0 ? $gf : '<span style="color: #000; font-weight: bold;">0</span>')."</th>";
                                                        }
                                                        $totalOverall = $maleTotal + $femaleTotal;
                                                        echo "<th class='text-center'>".($totalOverall != 0 ? $totalOverall : '<span style="color: #000; font-weight: bold;">0</span>')."</th>";
                                                        ?>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>


                        <!-- Province Ethnicity Gender Counts -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#provinceEthnicityCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Province, Ethnicity and Gender Distribution</a>
                                </h4>
                            </div>
                            <div id="provinceEthnicityCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="provinceEthnicitySearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="provinceEthnicityCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="provinceEthnicityCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="provinceEthnicityExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="provinceEthnicityPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="provinceEthnicityPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="provinceEthnicityTable">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center; background-color: #f5f5f5;">Province</th>
                                                    <th colspan="2" style="text-align: center;">Brahmin</th>
                                                    <th colspan="2" style="text-align: center;">Chhetri</th>
                                                    <th colspan="2" style="text-align: center;">Dalit</th>
                                                    <th colspan="2" style="text-align: center;">Janajati</th>
                                                    <th colspan="2" style="text-align: center;">Madhesi</th>
                                                    <th colspan="2" style="text-align: center;">Muslim</th>
                                                    <th colspan="2" style="text-align: center;">Tharu</th>
                                                    <th colspan="2" style="text-align: center;">Others</th>
                                                    <th style="text-align: center;">Total</th>
                                                </tr>
                                                <tr>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                    <th style="text-align: center;">M</th>
                                                    <th style="text-align: center;">F</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ethnicity_totals = [
                                                    'Brahmin' => ['male' => 0, 'female' => 0],
                                                    'Chhetri' => ['male' => 0, 'female' => 0],
                                                    'Dalit' => ['male' => 0, 'female' => 0],
                                                    'Janajati' => ['male' => 0, 'female' => 0],
                                                    'Madhesi' => ['male' => 0, 'female' => 0],
                                                    'Muslim' => ['male' => 0, 'female' => 0],
                                                    'Tharu' => ['male' => 0, 'female' => 0],
                                                    'Others' => ['male' => 0, 'female' => 0]
                                                ];
                                                $grand_total_male = 0;
                                                $grand_total_female = 0;
                                                
                                                if (isset($province_ethnicity_counts) && !empty($province_ethnicity_counts)) {
                                                    foreach ($province_ethnicity_counts as $province_data) {
                                                        ?>
                                                        <tr>
                                                            <td style="font-weight: bold; "><?php echo $province_data['province']; ?></td>
                                                            
                                                            <?php foreach (['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'] as $ethnicity): ?>
                                                                <td style="text-align: center;">
                                                                    <?php echo $province_data['ethnicities'][$ethnicity]['male']; ?>
                                                                </td>
                                                                <td style="text-align: center; ">
                                                                    <?php echo $province_data['ethnicities'][$ethnicity]['female']; ?>
                                                                </td>
                                                                <?php
                                                                // Update totals
                                                                $ethnicity_totals[$ethnicity]['male'] += $province_data['ethnicities'][$ethnicity]['male'];
                                                                $ethnicity_totals[$ethnicity]['female'] += $province_data['ethnicities'][$ethnicity]['female'];
                                                                ?>
                                                            <?php endforeach; ?>
                                                            
                                                            <td style="text-align: center; font-weight: bold;">
                                                                <?php echo $province_data['total_male'] + $province_data['total_female']; ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $grand_total_male += $province_data['total_male'];
                                                        $grand_total_female += $province_data['total_female'];
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="font-weight: bold;">
                                                    <td style="text-align: center;">Total</td>
                                                    <?php foreach (['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'] as $ethnicity): ?>
                                                        <td style="text-align: center;">
                                                            <?php echo $ethnicity_totals[$ethnicity]['male']; ?>
                                                        </td>
                                                        <td style="text-align: center;">
                                                            <?php echo $ethnicity_totals[$ethnicity]['female']; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td style="text-align: center;">
                                                        <?php echo $grand_total_male + $grand_total_female; ?>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Faculty Level Program Local Counts -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#facultyLevelProgramCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Faculty, Level, Program and Local Level Distribution</a>
                                </h4>
                            </div>
                            <div id="facultyLevelProgramCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="facultyLevelProgramSearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="facultyLevelProgramCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="facultyLevelProgramCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="facultyLevelProgramExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="facultyLevelProgramPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="facultyLevelProgramPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="max-width: 100%;">
                                        <table class="table table-striped table-bordered table-hover" id="facultyLevelProgramTable">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="vertical-align: middle;">S.No.</th>
                                                    <th rowspan="2" style="vertical-align: middle;">Local Level</th>
                                                    <?php
                                                    // Get unique faculties
                                                    $faculties = array();
                                                    $faculty_programs = array();
                                                    foreach ($faculty_level_program_local_counts as $count) {
                                                        if (!in_array($count['faculty'], $faculties)) {
                                                            $faculties[] = $count['faculty'];
                                                        }
                                                        if (!isset($faculty_programs[$count['faculty']])) {
                                                            $faculty_programs[$count['faculty']] = array();
                                                        }
                                                        if (!in_array($count['program'], $faculty_programs[$count['faculty']])) {
                                                            $faculty_programs[$count['faculty']][] = $count['program'];
                                                        }
                                                    }
                                                
                                                    // Print faculty headers
                                                    foreach ($faculties as $faculty) {
                                                        $program_count = count($faculty_programs[$faculty]);
                                                        echo '<th colspan="' . $program_count . '" class="text-center">' . $faculty . '</th>';
                                                    }
                                                    ?>
                                                    <th rowspan="2" style="vertical-align: middle;">Total</th>
                                                </tr>
                                                <tr>
                                                    <?php
                                                    // Print program headers under each faculty
                                                    foreach ($faculties as $faculty) {
                                                        foreach ($faculty_programs[$faculty] as $program) {
                                                            echo '<th class="text-center">' . $program . '</th>';
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get unique local levels
                                                $local_levels = array();
                                                foreach ($faculty_level_program_local_counts as $count) {
                                                    if (!in_array($count['local_level'], $local_levels)) {
                                                        $local_levels[] = $count['local_level'];
                                                    }
                                                }
                                            
                                                // Create data matrix
                                                $data_matrix = array();
                                                foreach ($faculty_level_program_local_counts as $count) {
                                                    $data_matrix[$count['local_level']][$count['faculty']][$count['program']] = $count['total'];
                                                }
                                            
                                                $sn = 1;
                                                foreach ($local_levels as $local_level) {
                                                    echo '<tr>';
                                                    echo '<td>' . $sn++ . '</td>';
                                                    echo '<td>' . $local_level . '</td>';
                                                
                                                    $row_total = 0;
                                                    foreach ($faculties as $faculty) {
                                                        foreach ($faculty_programs[$faculty] as $program) {
                                                            $count = isset($data_matrix[$local_level][$faculty][$program]) ?
                                                                    $data_matrix[$local_level][$faculty][$program] : 0;
                                                            echo '<td class="text-center">' . $count . '</td>';
                                                            $row_total += $count;
                                                        }
                                                    }
                                                    echo '<td class="text-center"><strong>' . $row_total . '</strong></td>';
                                                    echo '</tr>';
                                                }
                                            
                                                // Calculate and display grand total row
                                                echo '<tr>';
                                                echo '<td colspan="2" class="text-center"><strong>Grand Total</strong></td>';
                                            
                                                $grand_total = 0;
                                                foreach ($faculties as $faculty) {
                                                    foreach ($faculty_programs[$faculty] as $program) {
                                                        $program_total = 0;
                                                        foreach ($local_levels as $local_level) {
                                                            if (isset($data_matrix[$local_level][$faculty][$program])) {
                                                                $program_total += $data_matrix[$local_level][$faculty][$program];
                                                            }
                                                        }
                                                        echo '<td class="text-center"><strong>' . $program_total . '</strong></td>';
                                                        $grand_total += $program_total;
                                                    }
                                                }
                                                echo '<td class="text-center"><strong>' . $grand_total . '</strong></td>';
                                                echo '</tr>';
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Graduated Students Counts -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#graduatedStudentsCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Graduated Students by Faculty, Program, Gender and Ethnicity</a>
                                </h4>
                            </div>
                            <div id="graduatedStudentsCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="graduatedStudentsSearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="graduatedStudentsCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="graduatedStudentsCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="graduatedStudentsExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="graduatedStudentsPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="graduatedStudentsPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="graduatedStudentsTable" style="font-size: 12px;">
                                            <thead>
                                                <tr style="background-color: #5bc0de; color: white;">
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 40px;">S.No.</th>
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 200px;">Faculty/Program</th>
                                                    <?php
                                                    $ethnicities = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];
                                                    foreach ($ethnicities as $ethnicity) {
                                                        echo '<th colspan="2" style="text-align: center;">' . $ethnicity . '</th>';
                                                    }
                                                    ?>
                                                    <th rowspan="2" style="vertical-align: middle; text-align: center;">Total</th>
                                                </tr>
                                                <tr>
                                                    <?php
                                                    foreach ($ethnicities as $ethnicity) {
                                                        echo '<th style="text-align: center;">Male</th>';
                                                        echo '<th style="text-align: center;">Female</th>';
                                                    }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $faculties = array_unique(array_column($graduated_students_counts, 'faculty'));
                                                $sn = 1;
                                                $grand_totals = array_fill_keys($ethnicities, ['male' => 0, 'female' => 0]);
                                                $overall_total = 0;

                                                foreach ($faculties as $faculty) {
                                                    // Get all programs for this faculty
                                                    $faculty_programs = array_unique(array_map(function($item) use ($faculty) {
                                                        return $item['program'];
                                                    }, array_filter($graduated_students_counts, function($item) use ($faculty) {
                                                        return $item['faculty'] === $faculty;
                                                    })));

                                                    // Faculty row
                                                    echo '<tr style="font-weight: bold;">';
                                                    echo '<td style="text-align: center;">' . $sn++ . '</td>';
                                                    echo '<td>' . $faculty . '</td>';

                                                    $faculty_totals = array_fill_keys($ethnicities, ['male' => 0, 'female' => 0]);
                                                    $faculty_total = 0;

                                                    // Calculate faculty totals
                                                    foreach ($ethnicities as $ethnicity) {
                                                        $male = 0;
                                                        $female = 0;
                                                        foreach ($graduated_students_counts as $count) {
                                                            if ($count['faculty'] === $faculty) {
                                                                // If ethnicity is not specified or doesn't match standard ethnicities, add to Others
                                                                if (($count['ethnicity'] === $ethnicity) ||
                                                                    ($ethnicity === 'Others' && !in_array($count['ethnicity'], $ethnicities))) {
                                                                    $male += $count['male'];
                                                                    $female += $count['female'];
                                                                }
                                                            }
                                                        }
                                                        $faculty_totals[$ethnicity]['male'] = $male;
                                                        $faculty_totals[$ethnicity]['female'] = $female;
                                                        $faculty_total += $male + $female;

                                                        // Add to grand totals
                                                        $grand_totals[$ethnicity]['male'] += $male;
                                                        $grand_totals[$ethnicity]['female'] += $female;

                                                        echo '<td style="text-align: center;">' . $male . '</td>';
                                                        echo '<td style="text-align: center;">' . $female . '</td>';
                                                    }
                                                    $overall_total += $faculty_total;
                                                    echo '<td style="text-align: center;">' . $faculty_total . '</td>';
                                                    echo '</tr>';

                                                    // Program rows
                                                    foreach ($faculty_programs as $program) {
                                                        echo '<tr>';
                                                        echo '<td></td>';
                                                        echo '<td style="padding-left: 30px;">' . $program . '</td>';

                                                        $program_total = 0;
                                                        foreach ($ethnicities as $ethnicity) {
                                                            $male = 0;
                                                            $female = 0;
                                                            foreach ($graduated_students_counts as $count) {
                                                                if ($count['faculty'] === $faculty && $count['program'] === $program) {
                                                                    // If ethnicity is not specified or doesn't match standard ethnicities, add to Others
                                                                    if (($count['ethnicity'] === $ethnicity) ||
                                                                        ($ethnicity === 'Others' && !in_array($count['ethnicity'], $ethnicities))) {
                                                                        $male += $count['male'];
                                                                        $female += $count['female'];
                                                                    }
                                                                }
                                                            }
                                                            $program_total += $male + $female;
                                                            echo '<td style="text-align: center;">' . $male . '</td>';
                                                            echo '<td style="text-align: center;">' . $female . '</td>';
                                                        }
                                                        echo '<td style="text-align: center;">' . $program_total . '</td>';
                                                        echo '</tr>';
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #5bc0de; color: white; font-weight: bold;">
                                                    <td colspan="2" style="text-align: center;">Grand Total</td>
                                                    <?php
                                                    foreach ($ethnicities as $ethnicity) {
                                                        echo '<td style="text-align: center;">' . $grand_totals[$ethnicity]['male'] . '</td>';
                                                        echo '<td style="text-align: center;">' . $grand_totals[$ethnicity]['female'] . '</td>';
                                                    }
                                                    ?>
                                                    <td style="text-align: center;"><?php echo $overall_total; ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>



                         <!-- Pass Rates -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#passRatesCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Pass Rates by Level and Faculty</a>
                                </h4>
                            </div>
                            <div id="passRatesCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="passRatesSearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="passRatesCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="passRatesCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="passRatesExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="passRatesPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="passRatesPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($pass_rates) && !empty($pass_rates) && isset($pass_rates['data']) && !empty($pass_rates['data'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="passRatesTable">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2" style="vertical-align: middle;">Level</th>
                                                        <?php foreach ($pass_rates['sections'] as $section): ?>
                                                            <th colspan="2" class="text-center"><?php echo $section['section']; ?></th>
                                                        <?php endforeach; ?>
                                                        <th rowspan="2" style="vertical-align: middle;">Total Pass %</th>
                                                    </tr>
                                                    <tr>
                                                        <?php foreach ($pass_rates['sections'] as $section): ?>
                                                            <th class="text-center">Male %</th>
                                                            <th class="text-center">Female %</th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pass_rates['data'] as $level => $faculties): ?>
                                                        <tr>
                                                            <td><strong><?php echo $level; ?></strong></td>
                                                            <?php 
                                                            $level_total_students = 0;
                                                            $level_total_passed = 0;
                                                            
                                                            foreach ($pass_rates['sections'] as $section): 
                                                                $faculty_name = $section['section'];
                                                                if (isset($faculties[$faculty_name])):
                                                                    $level_total_students += $faculties[$faculty_name]['total_students'];
                                                                    $level_total_passed += $faculties[$faculty_name]['total_passed'];
                                                            ?>
                                                                <td class="text-center"><?php echo $faculties[$faculty_name]['male_pass']; ?></td>
                                                                <td class="text-center"><?php echo $faculties[$faculty_name]['female_pass']; ?></td>
                                                            <?php else: ?>
                                                                <td class="text-center">-</td>
                                                                <td class="text-center">-</td>
                                                            <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            
                                                            <td class="text-center">
                                                                <strong>
                                                                    <?php 
                                                                    if ($level_total_students > 0) {
                                                                        echo number_format(($level_total_passed * 100.0) / $level_total_students, 2) . '%';
                                                                    } else {
                                                                        echo 'N/A';
                                                                    }
                                                                    ?>
                                                                </strong>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No pass rate data available for the selected criteria.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>


                        <!-- Staff Details -->
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#staffDetailsCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Staff Details</a>
                                </h4>
                            </div>
                            <div id="staffDetailsCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="staffDetailsSearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="staffDetailsCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="staffDetailsCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="staffDetailsExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="staffDetailsPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="staffDetailsPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="overflow-x: auto;">
                                        <table class="table table-bordered table-condensed table-hover" id="staffDetailsTable" style="margin-bottom: 0; font-size: 12px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width: 150px;">Name</th>
                                                    <?php 
                                                    // Get all possible positions (even those without staff)
                                                    $all_positions = isset($all_positions) ? $all_positions : array_keys($staff_details_by_position ?? []);
                                                    foreach ($all_positions as $position): ?>
                                                        <th colspan="3" style="text-align: center; background-color: #f5f5f5; min-width: 180px;">
                                                            <?php echo htmlspecialchars($position); ?>
                                                            <?php if (isset($staff_details_by_position[$position])): ?>
                                                                (<?php echo count($staff_details_by_position[$position]); ?>)
                                                            <?php else: ?>
                                                                (0)
                                                            <?php endif; ?>
                                                        </th>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($all_positions as $position): ?>
                                                        <th style="width: 60px;">Contract</th>
                                                        <th style="width: 60px;">Qualification</th>
                                                        <th style="width: 80px;">Appointment</th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Get all unique staff names across all positions
                                                $all_staff = [];
                                                if (isset($staff_details_by_position)) {
                                                    foreach ($staff_details_by_position as $position => $staff_list) {
                                                        foreach ($staff_list as $staff) {
                                                            if (isset($staff['name'])) {
                                                                $all_staff[$staff['name']] = $staff['name'];
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                if (!empty($all_staff)): 
                                                    foreach ($all_staff as $staff_name): ?>
                                                        <tr>
                                                            <td style="font-weight: bold; color: #337ab7; white-space: nowrap;">
                                                                <?php echo htmlspecialchars($staff_name); ?>
                                                            </td>
                                                            <?php foreach ($all_positions as $position): 
                                                                $staff_data = null;
                                                                if (isset($staff_details_by_position[$position])) {
                                                                    foreach ($staff_details_by_position[$position] as $staff) {
                                                                        if (isset($staff['name']) && $staff['name'] === $staff_name) {
                                                                            $staff_data = $staff;
                                                                            break;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <?php if ($staff_data): ?>
                                                                    <td><?php echo htmlspecialchars($staff_data['job_type'] ?: '-'); ?></td>
                                                                    <td><?php echo htmlspecialchars($staff_data['qualification'] ?: '-'); ?></td>
                                                                    <td><?php echo $staff_data['appointment_date'] ? date('d-m-Y', strtotime($staff_data['appointment_date'])) : '-'; ?></td>
                                                                <?php else: ?>
                                                                    <td colspan="3" style="text-align: center; color: #ccc;">-</td>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="<?php echo (count($all_positions) * 3) + 1; ?>" class="text-center">
                                                            <i class="fa fa-info-circle"></i> No staff details available.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                         

                          <!-- Staff by Ethnicity and Gender -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#staffEthnicityCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Staff by Ethnicity and Gender</a>
                                    </h4>
                                </div>
                                <div id="staffEthnicityCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-6">
                                                <input type="text" class="form-control input-sm" id="staffEthnicitySearch" placeholder="Search..." style="height: 30px;">
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" id="staffEthnicityCopyBtn">Copy</button>
                                                    <button class="btn btn-default" id="staffEthnicityCsvBtn">CSV</button>
                                                    <button class="btn btn-default" id="staffEthnicityExcelBtn">Excel</button>
                                                    <button class="btn btn-default" id="staffEthnicityPdfBtn">PDF</button>
                                                    <button class="btn btn-default" id="staffEthnicityPrintBtn">Print</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="staffEthnicityTable" style="font-size: 12px;">
                                                <thead>
                                                    <tr style="color: white;">
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 40px;">S.No.</th>
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center; min-width: 200px;">Type</th>
                                                        <?php
                                                        $ethnicities = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];
                                                        foreach ($ethnicities as $ethnicity) {
                                                            echo '<th colspan="2" style="text-align: center;">' . $ethnicity . '</th>';
                                                        }
                                                        ?>
                                                        <th rowspan="2" style="vertical-align: middle; text-align: center;">Total</th>
                                                    </tr>
                                                    <tr style="color: white;">
                                                        <?php
                                                        foreach ($ethnicities as $ethnicity) {
                                                            echo '<th style="text-align: center;">Male</th>';
                                                            echo '<th style="text-align: center;">Female</th>';
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sn = 1;
                                                    $grand_totals = array_fill_keys($ethnicities, ['male' => 0, 'female' => 0]);
                                                    $overall_total = 0;

                                                    // Initialize counts for each staff type and ethnicity
                                                    $staff_type_counts = [
                                                        'Teaching' => array_fill_keys($ethnicities, ['male' => 0, 'female' => 0]),
                                                        'Non-Teaching' => array_fill_keys($ethnicities, ['male' => 0, 'female' => 0])
                                                    ];
                                                
                                                    // Calculate totals for each ethnicity and staff type
                                                    foreach ($staff_ethnicity_counts as $count) {
                                                        $target_ethnicity = in_array($count['ethnicity'], $ethnicities) ? $count['ethnicity'] : 'Others';
                                                        $staff_type = $count['staff_type'];
                                                        $staff_type_counts[$staff_type][$target_ethnicity]['male'] += $count['male'];
                                                        $staff_type_counts[$staff_type][$target_ethnicity]['female'] += $count['female'];
                                                    }

                                                    // Display row for teaching staff counts
                                                    echo '<tr>';
                                                    echo '<td style="text-align: center;">' . $sn++ . '</td>';
                                                    echo '<td>Teaching Staff</td>';
                                                
                                                    $teaching_total = 0;
                                                    foreach ($ethnicities as $ethnicity) {
                                                        $male = isset($staff_type_counts['Teaching'][$ethnicity]['male']) ? $staff_type_counts['Teaching'][$ethnicity]['male'] : 0;
                                                        $female = isset($staff_type_counts['Teaching'][$ethnicity]['female']) ? $staff_type_counts['Teaching'][$ethnicity]['female'] : 0;
                                                    
                                                        echo '<td style="text-align: center;">' . $male . '</td>';
                                                        echo '<td style="text-align: center;">' . $female . '</td>';
                                                    
                                                        $teaching_total += $male + $female;
                                                        $grand_totals[$ethnicity]['male'] += $male;
                                                        $grand_totals[$ethnicity]['female'] += $female;
                                                    }
                                                    echo '<td style="text-align: center;">' . $teaching_total . '</td>';
                                                    echo '</tr>';

                                                    // Display row for non-teaching staff counts
                                                    echo '<tr>';
                                                    echo '<td style="text-align: center;">' . $sn++ . '</td>';
                                                    echo '<td>Non-Teaching Staff</td>';
                                                
                                                    $non_teaching_total = 0;
                                                    foreach ($ethnicities as $ethnicity) {
                                                        $male = isset($staff_type_counts['Non-Teaching'][$ethnicity]['male']) ? $staff_type_counts['Non-Teaching'][$ethnicity]['male'] : 0;
                                                        $female = isset($staff_type_counts['Non-Teaching'][$ethnicity]['female']) ? $staff_type_counts['Non-Teaching'][$ethnicity]['female'] : 0;
                                                    
                                                        echo '<td style="text-align: center;">' . $male . '</td>';
                                                        echo '<td style="text-align: center;">' . $female . '</td>';
                                                    
                                                        $non_teaching_total += $male + $female;
                                                        $grand_totals[$ethnicity]['male'] += $male;
                                                        $grand_totals[$ethnicity]['female'] += $female;
                                                    }
                                                    echo '<td style="text-align: center;">' . $non_teaching_total . '</td>';
                                                    echo '</tr>';

                                                    $overall_total = $teaching_total + $non_teaching_total;

                                                    // Display grand total row with light blue background
                                                    echo '<tr>';
                                                    echo '<td colspan="2" style="text-align: center;">Grand Total</td>';
                                                
                                                    $total_male = 0;
                                                    $total_female = 0;
                                                    foreach ($ethnicities as $ethnicity) {
                                                        $male = $grand_totals[$ethnicity]['male'];
                                                        $female = $grand_totals[$ethnicity]['female'];
                                                        $total_male += $male;
                                                        $total_female += $female;
                                                        echo '<td style="text-align: center;">' . $male . '</td>';
                                                        echo '<td style="text-align: center;">' . $female . '</td>';
                                                    }
                                                    echo '<td style="text-align: center;">' . $overall_total . '</td>';
                                                    echo '</tr>';
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>



                         <!-- Staff Position Counts -->
                         <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-target="#staffPositionCollapse" style="cursor: pointer;">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle">Staff by Contract Type and Position/Gender</a>
                                </h4>
                            </div>
                            <div id="staffPositionCollapse" class="panel-collapse collapse">
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 10px;">
                                        <div class="col-xs-6">
                                            <input type="text" class="form-control input-sm" id="staffPositionSearch" placeholder="Search..." style="height: 30px;">
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-default" id="staffPositionCopyBtn">Copy</button>
                                                <button class="btn btn-default" id="staffPositionCsvBtn">CSV</button>
                                                <button class="btn btn-default" id="staffPositionExcelBtn">Excel</button>
                                                <button class="btn btn-default" id="staffPositionPdfBtn">PDF</button>
                                                <button class="btn btn-default" id="staffPositionPrintBtn">Print</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="overflow-x: auto;">
                                        <table class="table table-bordered table-condensed table-hover" id="staffPositionTable" style="margin-bottom: 0; font-size: 12px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width: 20%;">Contract Type</th>
                                                    <?php
                                                    // Get all positions (including those with no data)
                                                    $all_positions = [];
                                                    if (isset($positionlist) && !empty($positionlist)) {
                                                        foreach ($positionlist as $pos) {
                                                            $all_positions[] = $pos['name'];
                                                        }
                                                    }
                                                    // Also add any positions that might be in the data but not in positionlist
                                                    if (isset($staff_position_counts) && !empty($staff_position_counts)) {
                                                        foreach ($staff_position_counts as $count) {
                                                            if (!in_array($count['position'], $all_positions)) {
                                                                $all_positions[] = $count['position'];
                                                            }
                                                        }
                                                    }
                                                    // Sort positions alphabetically
                                                    sort($all_positions);
                                                    
                                                    // Add headers for each position with gender subheaders
                                                    foreach ($all_positions as $position): ?>
                                                        <th colspan="2" style="text-align: center; background-color: #f5f5f5;">
                                                            <?php echo htmlspecialchars($position); ?>
                                                        </th>
                                                    <?php endforeach; ?>
                                                    <th rowspan="2" style="text-align: center; background-color: #f5f5f5; width: 8%;">Total</th>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($all_positions as $position): ?>
                                                        <th style="width: 5%; text-align: center; background-color: #f9f9f9;">M</th>
                                                        <th style="width: 5%; text-align: center; background-color: #f9f9f9;">F</th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get all contract types (job types)
                                                $all_job_types = [];
                                                if (isset($staff_position_counts) && !empty($staff_position_counts)) {
                                                    foreach ($staff_position_counts as $count) {
                                                        if (!in_array($count['job_type'], $all_job_types)) {
                                                            $all_job_types[] = $count['job_type'];
                                                        }
                                                    }
                                                }
                                                // Sort contract types alphabetically
                                                sort($all_job_types);
                                                
                                                // Organize data by contract type and position
                                                $organized_data = [];
                                                if (isset($staff_position_counts)) {
                                                    foreach ($staff_position_counts as $count) {
                                                        $job_type = $count['job_type'];
                                                        $position = $count['position'];
                                                        
                                                        if (!isset($organized_data[$job_type])) {
                                                            $organized_data[$job_type] = [];
                                                        }
                                                        $organized_data[$job_type][$position] = [
                                                            'male' => $count['male'],
                                                            'female' => $count['female'],
                                                            'total' => $count['total']
                                                        ];
                                                    }
                                                }
                                                
                                                // Calculate grand totals
                                                $grand_totals = [
                                                    'positions' => array_fill_keys($all_positions, ['male' => 0, 'female' => 0]),
                                                    'contracts' => array_fill_keys($all_job_types, 0),
                                                    'overall' => 0
                                                ];
                                                
                                                // Display each contract type
                                                foreach ($all_job_types as $job_type): 
                                                    $contract_data = $organized_data[$job_type] ?? [];
                                                    $contract_total = 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($job_type); ?></td>
                                                        <?php foreach ($all_positions as $position): 
                                                            $position_data = $contract_data[$position] ?? null;
                                                            if ($position_data): 
                                                                $contract_total += $position_data['total'];
                                                                $grand_totals['positions'][$position]['male'] += $position_data['male'];
                                                                $grand_totals['positions'][$position]['female'] += $position_data['female'];
                                                                ?>
                                                                <td style="text-align: center;"><?php echo $position_data['male']; ?></td>
                                                                <td style="text-align: center;"><?php echo $position_data['female']; ?></td>
                                                            <?php else: ?>
                                                                <td style="text-align: center;">0</td>
                                                                <td style="text-align: center;">0</td>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                        <!-- Contract type total -->
                                                        <td style="text-align: center; font-weight: bold;">
                                                            <?php 
                                                            echo $contract_total;
                                                            $grand_totals['contracts'][$job_type] = $contract_total;
                                                            $grand_totals['overall'] += $contract_total;
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                            <?php
                            // Preprocess rowspans
                            $level_rowspans = [];
                            $faculty_rowspans = [];

                            foreach ($campus_enrollment_summary as $level => $faculties) {
                                $level_count = 0;
                                foreach ($faculties as $faculty => $programs) {
                                    $faculty_count = count($programs);
                                    $faculty_rowspans[$level][$faculty] = $faculty_count;
                                    $level_count += $faculty_count;
                                }
                                $level_rowspans[$level] = $level_count;
                            }
                            ?>

                            <!-- Data quality summary (actionable) -->
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-md-6">
                                    <div class="small-box bg-green">
                                        <div class="inner">
                                            <h3><?php echo isset($ugc_dq_ready_count) ? (int) $ugc_dq_ready_count : '—'; ?></h3>
                                            <p>Total students ready for sync <span class="hidden-xs">(no UGC data gaps)</span></p>
                                        </div>
                                        <div class="icon"><i class="fa fa-check-circle"></i></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small-box bg-yellow">
                                        <div class="inner">
                                            <h3><?php echo isset($ugc_dq_gap_count) ? (int) $ugc_dq_gap_count : '—'; ?></h3>
                                            <p>Total students with gaps <span class="hidden-xs">(fix profile, then sync)</span></p>
                                        </div>
                                        <div class="icon"><i class="fa fa-warning"></i></div>
                                    </div>
                                </div>
                            </div>

                            <!-- UGC / HEMIS compliance (Student Enrollment Report scope) -->
                            <div class="panel panel-info">
                                <div class="panel-heading" data-toggle="collapse" data-target="#ugcComplianceCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle"><i class="fa fa-check-square-o"></i> UGC / HEMIS — Enrollment vs sync</a>
                                    </h4>
                                </div>
                                <div id="ugcComplianceCollapse" class="panel-collapse collapse in">
                                    <div class="panel-body">
                                        <p class="text-muted small" style="margin-top:0;">
                                            <strong>Campus Enrollment Summary</strong> uses current session, active students, <strong>Gender</strong>, mapped <strong>Ethnicity</strong> (prefer <code>ethnicity</code>, else <code>cast</code>),
                                            <strong>Program</strong> (from <code>student_session</code> with <code>class_sections</code> fallback), and class/section for level/faculty.
                                            <strong>Academic cohort</strong> is tracked per student as <code>admission_year</code> (see Data Quality Report if missing).
                                        </p>
                                        <table class="table table-bordered table-condensed" style="max-width:720px;">
                                            <tbody>
                                                <tr><th>Active students (current session)</th><td><strong><?php echo (int) (isset($ugc_session_student_total) ? $ugc_session_student_total : 0); ?></strong></td></tr>
                                                <tr><th>Enrollment matrix headcount (male+female cells)</th><td><strong><?php echo (int) (isset($ugc_matrix_headcount) ? $ugc_matrix_headcount : 0); ?></strong></td></tr>
                                                <tr><th>GPI (Female Total ÷ Male Total, matrix scope)</th><td><strong><?php
                                                    $gf = isset($ugc_gpi_female) ? (int) $ugc_gpi_female : 0;
                                                    $gm = isset($ugc_gpi_male) ? (int) $ugc_gpi_male : 0;
                                                    echo 'F=' . $gf . ', M=' . $gm;
                                                    echo ($gm > 0 && isset($ugc_gpi_ratio)) ? ' &nbsp;|&nbsp; GPI=' . htmlspecialchars((string) $ugc_gpi_ratio, ENT_QUOTES, 'UTF-8') : '';
                                                ?></strong></td></tr>
                                                <tr><th>HEMIS &quot;Synced&quot; (has hemis_student_id, no sync error)</th><td><strong><?php echo isset($ugc_hemis_synced_total) && $ugc_hemis_synced_total !== null ? (int) $ugc_hemis_synced_total : '—'; ?></strong></td></tr>
                                            </tbody>
                                        </table>
                                        <p class="small text-muted" style="margin-bottom:0;">
                                            Matrix total usually matches active students who are Male/Female with resolvable program. It will not match HEMIS synced count until all students are synced and error-free.
                                            Compare <strong>Active students</strong> with <strong>Synced</strong> for UGC coverage; use the Data Quality Report below for exceptions.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($ugc_data_quality_rows)) { ?>
                            <div class="panel panel-warning">
                                <div class="panel-heading" data-toggle="collapse" data-target="#ugcDataQualityCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle"><i class="fa fa-exclamation-triangle"></i> Data Quality Report — students blocking full compliance</a>
                                    </h4>
                                </div>
                                <div id="ugcDataQualityCollapse" class="panel-collapse collapse">
                                    <div class="panel-body" style="overflow-x:auto;">
                                        <p class="text-muted small" style="overflow:hidden;">
                                            <span class="pull-right">
                                                <a class="btn btn-default btn-xs" href="<?php echo site_url('report/ugc_data_quality_csv'); ?>">
                                                    <i class="fa fa-download"></i> Download CSV of missing data
                                                </a>
                                            </span>
                                            Showing up to 600 rows on this page with at least one issue (gender, ethnicity, program, admission year, HEMIS id/error). CSV export includes more rows for offline follow-up.
                                        </p>
                                        <table class="table table-striped table-bordered table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>Admission No.</th>
                                                    <th>Name</th>
                                                    <th>Issues</th>
                                                    <th style="width:90px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ugc_data_quality_rows as $entry) {
                                                    $r = $entry['row'];
                                                    $name = trim(($r['firstname'] ?? '') . ' ' . ($r['middlename'] ?? '') . ' ' . ($r['lastname'] ?? ''));
                                                    $sid = isset($r['id']) ? (int) $r['id'] : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($r['admission_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <td><ul class="small" style="margin:0;padding-left:18px;"><?php
                                                            foreach ($entry['issues'] as $issue) {
                                                                echo '<li>' . htmlspecialchars($issue, ENT_QUOTES, 'UTF-8') . '</li>';
                                                            }
                                                        ?></ul></td>
                                                        <td>
                                                            <?php if ($sid > 0) { ?>
                                                                <a class="btn btn-primary btn-xs" href="<?php echo site_url('student/edit/' . $sid); ?>" target="_blank" rel="noopener noreferrer">Edit</a>
                                                            <?php } else { ?>
                                                                <span class="text-muted">&mdash;</span>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="alert alert-success small"><i class="fa fa-check"></i> Data Quality: no blocking issues found for sampled active students (or hemis columns not installed).</div>
                            <?php } ?>

                            <!-- Campus Enrollment Summary Report -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#campusEnrollmentCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Campus Enrollment Summary (Student Enrollment)</a>
                                    </h4>
                                </div>
                                <div id="campusEnrollmentCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-6">
                                                <input type="text" class="form-control input-sm" id="campusEnrollmentSearch" placeholder="Search..." style="height: 30px;">
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" id="copyBtn">Copy</button>
                                                    <button class="btn btn-default" id="csvBtn">CSV</button>
                                                    <button class="btn btn-default" id="excelBtn">Excel</button>
                                                    <button class="btn btn-default" id="pdfBtn">PDF</button>
                                                    <button class="btn btn-default" id="printBtn">Print</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div style="overflow-x: auto;">
                                            <table class="table table-striped table-bordered table-hover" id="campusEnrollmentTable" style="margin-bottom: 0;">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">Level</th>
                                                        <th rowspan="2">Faculty</th>
                                                        <th rowspan="2">Program</th>
                                                        <?php foreach ($ethnicitylist as $ethnicity): ?>
                                                            <th colspan="2" class="text-center"><?php echo $ethnicity['name']; ?></th>
                                                        <?php endforeach; ?>
                                                        <th rowspan="2">Grand Total</th>
                                                    </tr>
                                                    <tr>
                                                        <?php foreach ($ethnicitylist as $ethnicity): ?>
                                                            <th>Male</th>
                                                            <th>Female</th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $grand_total_all = 0;
                                                    foreach ($campus_enrollment_summary as $level => $faculties):
                                                        $first_level = true;
                                                        foreach ($faculties as $faculty => $programs):
                                                            $first_faculty = true;
                                                            foreach ($programs as $program => $ethnicities):
                                                                $row_total = 0;
                                                    ?>
                                                    <tr>
                                                        <?php if ($first_level): ?>
                                                            <td rowspan="<?php echo $level_rowspans[$level]; ?>"><?php echo $level; ?></td>
                                                            <?php $first_level = false; ?>
                                                        <?php endif; ?>

                                                        <?php if ($first_faculty): ?>
                                                            <td rowspan="<?php echo $faculty_rowspans[$level][$faculty]; ?>"><?php echo $faculty; ?></td>
                                                            <?php $first_faculty = false; ?>
                                                        <?php endif; ?>

                                                        <td><?php echo $program; ?></td>
                                                        <?php foreach ($ethnicitylist as $ethnicity): 
                                                            $eth_name = $ethnicity['name'];
                                                            $male = isset($ethnicities[$eth_name]['male']) ? $ethnicities[$eth_name]['male'] : 0;
                                                            $female = isset($ethnicities[$eth_name]['female']) ? $ethnicities[$eth_name]['female'] : 0;
                                                            $row_total += ($male + $female);
                                                        ?>
                                                            <td><?php echo $male; ?></td>
                                                            <td><?php echo $female; ?></td>
                                                        <?php endforeach; ?>
                                                        <td><strong><?php echo $row_total; ?></strong></td>
                                                    </tr>
                                                    <?php 
                                                                $grand_total_all += $row_total;
                                                            endforeach;
                                                        endforeach;
                                                    endforeach;

                                                    if (empty($campus_enrollment_summary)):
                                                    ?>
                                                        <tr>
                                                            <td colspan="<?php echo 3 + (count($ethnicitylist) * 2) + 1; ?>" class="text-center">No enrollment data found</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Dropout Statistics -->
                            <div class="panel panel-default">
                                <div class="panel-heading" data-toggle="collapse" data-target="#dropoutStatsCollapse" style="cursor: pointer;">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle">Dropout Statistics by Program</a>
                                    </h4>
                                </div>
                                <div id="dropoutStatsCollapse" class="panel-collapse collapse">
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom: 10px;">
                                            <div class="col-xs-6">
                                                <input type="text" class="form-control input-sm" id="dropoutSearch" placeholder="Search..." style="height: 30px;">
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-default" id="dropoutCopyBtn">Copy</button>
                                                    <button class="btn btn-default" id="dropoutCsvBtn">CSV</button>
                                                    <button class="btn btn-default" id="dropoutExcelBtn">Excel</button>
                                                    <button class="btn btn-default" id="dropoutPdfBtn">PDF</button>
                                                    <button class="btn btn-default" id="dropoutPrintBtn">Print</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div style="overflow-x: auto;">
                                            <table class="table table-striped table-bordered table-hover" id="dropoutStatsTable" style="margin-bottom: 0;">
                                                <thead>
                                                    <tr>
                                                        <th>Program</th>
                                                        <th>Male Dropouts</th>
                                                        <th>Female Dropouts</th>
                                                        <th>Total Dropouts</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if (isset($dropout_counts) && !empty($dropout_counts)) {
                                                        foreach ($dropout_counts as $count) {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $count['program']; ?></td>
                                                                <td><?php echo $count['male_dropouts']; ?></td>
                                                                <td><?php echo $count['female_dropouts']; ?></td>
                                                                <td><?php echo $count['total_dropouts']; ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Initialize all tables as DataTables
        $('.table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            'pageLength': 50,
            // Hide the DataTables controls when table is collapsed
            'initComplete': function(settings, json) {
                var api = this.api();
                var tableId = $(api.table().node()).closest('.panel-collapse').attr('id');
                $('#' + tableId).on('hide.bs.collapse', function () {
                    $(api.buttons().container()).hide();
                }).on('show.bs.collapse', function () {
                    $(api.buttons().container()).show();
                });
            }
        });
        
        // Make the first panel expanded by default
        $('#facultyGenderCollapse').collapse('show');
        
        // Add click handler for panel headings
        $('.panel-heading').click(function() {
            // Collapse all other panels
            $('.panel-collapse').not($(this).data('target')).collapse('hide');
            // Toggle the clicked panel
            $($(this).data('target')).collapse('toggle');
        });
    });

$(document).ready(function() {
    // Search functionality
    $('#campusEnrollmentSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#campusEnrollmentTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard
    $('#copyBtn').click(function() {
        const table = $('#campusEnrollmentTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none') $(this).remove();
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Table copied to clipboard');
    });

    // Export to CSV
    $('#csvBtn').click(function() {
        const table = $('#campusEnrollmentTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "campus_enrollment.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel
    $('#excelBtn').click(function() {
        const table = $('#campusEnrollmentTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "campus_enrollment.xlsx");
    });

    // Export to PDF
    $('#pdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.autoTable({
            html: '#campusEnrollmentTable',
            styles: { fontSize: 8 },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1
        });
        
        doc.save('campus_enrollment.pdf');
    });

    // Print
    $('#printBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Table</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#campusEnrollmentTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // Search functionality for Dropout table
    $('#dropoutSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#dropoutStatsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard for Dropout table
    $('#dropoutCopyBtn').click(function() {
        const table = $('#dropoutStatsTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none') $(this).remove();
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Dropout table copied to clipboard');
    });

    // Export to CSV for Dropout table
    $('#dropoutCsvBtn').click(function() {
        const table = $('#dropoutStatsTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "dropout_statistics.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel for Dropout table
    $('#dropoutExcelBtn').click(function() {
        const table = $('#dropoutStatsTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "dropout_statistics.xlsx");
    });

    // Export to PDF for Dropout table
    $('#dropoutPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.autoTable({
            html: '#dropoutStatsTable',
            styles: { fontSize: 8 },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1
        });
        
        doc.save('dropout_statistics.pdf');
    });

    // Print for Dropout table
    $('#dropoutPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Dropout Table</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#dropoutStatsTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // Search functionality for Staff Position table
    $('#staffPositionSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#staffPositionTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard for Staff Position table
    $('#staffPositionCopyBtn').click(function() {
        const table = $('#staffPositionTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none') $(this).remove();
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Staff position table copied to clipboard');
    });

    // Export to CSV for Staff Position table
    $('#staffPositionCsvBtn').click(function() {
        const table = $('#staffPositionTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "staff_positions.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel for Staff Position table
    $('#staffPositionExcelBtn').click(function() {
        const table = $('#staffPositionTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "staff_positions.xlsx");
    });

    // Export to PDF for Staff Position table
    $('#staffPositionPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.autoTable({
            html: '#staffPositionTable',
            styles: { fontSize: 8 },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1
        });
        
        doc.save('staff_positions.pdf');
    });

    // Print for Staff Position table
    $('#staffPositionPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Staff Position Table</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#staffPositionTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // Search functionality for Staff Ethnicity table
    $('#staffEthnicitySearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#staffEthnicityTable tbody tr').filter(function() {
            // Skip the grand total row from filtering
            if ($(this).find('td:first').text() === 'Grand Total') {
                return true;
            }
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard for Staff Ethnicity table
    $('#staffEthnicityCopyBtn').click(function() {
        const table = $('#staffEthnicityTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none' && $(this).find('td:first').text() !== 'Grand Total') {
                $(this).remove();
            }
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Staff ethnicity table copied to clipboard');
    });

    // Export to CSV for Staff Ethnicity table
    $('#staffEthnicityCsvBtn').click(function() {
        const table = $('#staffEthnicityTable');
        const rows = table.find('tr:visible, tr.info').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "staff_ethnicity.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel for Staff Ethnicity table
    $('#staffEthnicityExcelBtn').click(function() {
        const table = $('#staffEthnicityTable');
        const rows = table.find('tr:visible, tr.info').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "staff_ethnicity.xlsx");
    });

    // Export to PDF for Staff Ethnicity table
    $('#staffEthnicityPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l'); // Landscape orientation
        
        doc.autoTable({
            html: '#staffEthnicityTable',
            styles: { 
                fontSize: 6,
                cellPadding: 2,
                overflow: 'linebreak'
            },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1,
            margin: { top: 10, right: 5, bottom: 10, left: 5 },
            pageBreak: 'auto',
            tableWidth: 'wrap'
        });
        
        doc.save('staff_ethnicity.pdf');
    });

    // Print for Staff Ethnicity table
    $('#staffEthnicityPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=1000');
        printWindow.document.write('<html><head><title>Print Staff Ethnicity Table</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('<style>@page { size: landscape; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#staffEthnicityTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // Search functionality for Staff Details table
    $('#staffDetailsSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#staffDetailsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard for Staff Details table
    $('#staffDetailsCopyBtn').click(function() {
        const table = $('#staffDetailsTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none') $(this).remove();
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Staff details copied to clipboard');
    });

    // Export to CSV for Staff Details table
    $('#staffDetailsCsvBtn').click(function() {
        const table = $('#staffDetailsTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "staff_details.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel for Staff Details table
    $('#staffDetailsExcelBtn').click(function() {
        const table = $('#staffDetailsTable');
        const rows = table.find('tr:visible').get().map(function(row) {
            return $(row).find('th, td').get().map(function(cell) {
                return $(cell).text().trim();
            });
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
        XLSX.writeFile(wb, "staff_details.xlsx");
    });

    // Export to PDF for Staff Details table
    $('#staffDetailsPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.autoTable({
            html: '#staffDetailsTable',
            styles: { 
                fontSize: 8,
                cellPadding: 3,
                overflow: 'linebreak'
            },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1,
            margin: { top: 10, right: 5, bottom: 10, left: 5 },
            pageBreak: 'auto',
            tableWidth: 'wrap'
        });
        
        doc.save('staff_details.pdf');
    });

    // Print for Staff Details table
    $('#staffDetailsPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Staff Details</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#staffDetailsTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // Search functionality for Pass Rates table
    $('#passRatesSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#passRatesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Copy to clipboard for Pass Rates table
    $('#passRatesCopyBtn').click(function() {
        const table = $('#passRatesTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none') $(this).remove();
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Pass rates copied to clipboard');
    });

    // Export to CSV for Pass Rates table
    $('#passRatesCsvBtn').click(function() {
        const table = $('#passRatesTable');
        const headers = [];
        const sections = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process sections
        table.find('thead tr:last th').each(function() {
            sections.push($(this).text().trim());
        });
        
        // Combine headers and sections
        const headerRow = [];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i % 2 === 0) {
                headerRow.push(header);
            } else {
                headerRow.push(sections[headerIndex++]);
            }
        });
        
        // Process data rows
        const rows = [headerRow, sections];
        table.find('tbody tr:visible').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "pass_rates.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Export to Excel for Pass Rates table
    $('#passRatesExcelBtn').click(function() {
        const table = $('#passRatesTable');
        const headers = [];
        const sections = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process sections
        table.find('thead tr:last th').each(function() {
            sections.push($(this).text().trim());
        });
        
        // Combine headers and sections
        const headerRow = [];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i % 2 === 0) {
                headerRow.push(header);
            } else {
                headerRow.push(sections[headerIndex++]);
            }
        });
        
        // Process data rows
        const rows = [headerRow, sections];
        table.find('tbody tr:visible').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Pass Rates");
        XLSX.writeFile(wb, "pass_rates.xlsx");
    });

    // Export to PDF for Pass Rates table
    $('#passRatesPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l'); // Landscape orientation
        
        doc.autoTable({
            html: '#passRatesTable',
            styles: { 
                fontSize: 7,
                cellPadding: 2,
                overflow: 'linebreak'
            },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1,
            margin: { top: 10, right: 5, bottom: 10, left: 5 },
            pageBreak: 'auto',
            tableWidth: 'wrap'
        });
        
        doc.save('pass_rates.pdf');
    });

    // Print for Pass Rates table
    $('#passRatesPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=1000');
        printWindow.document.write('<html><head><title>Print Pass Rates</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('<style>@page { size: landscape; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#passRatesTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });
});

$(document).ready(function() {
    // ========== Faculty Level Program Local Counts ==========
    $('#facultyLevelProgramSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#facultyLevelProgramTable tbody tr').filter(function() {
            // Skip the grand total row from filtering
            if ($(this).find('td:first').text() === 'Grand Total') {
                return true;
            }
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#facultyLevelProgramCopyBtn').click(function() {
        const table = $('#facultyLevelProgramTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none' && $(this).find('td:first').text() !== 'Grand Total') {
                $(this).remove();
            }
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Faculty/Program distribution copied to clipboard');
    });

    $('#facultyLevelProgramCsvBtn').click(function() {
        const table = $('#facultyLevelProgramTable');
        const headers = [];
        const programs = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process programs
        table.find('thead tr:last th').each(function() {
            programs.push($(this).text().trim());
        });
        
        // Combine headers and programs
        const headerRow = ['S.No.', 'Local Level'];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i > 1) { // Skip S.No. and Local Level
                if (programs[headerIndex]) {
                    headerRow.push(header + ' - ' + programs[headerIndex++]);
                }
            }
        });
        headerRow.push('Total');
        
        // Process data rows
        const rows = [headerRow];
        table.find('tbody tr:visible, tbody tr.info').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "faculty_program_distribution.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $('#facultyLevelProgramExcelBtn').click(function() {
        const table = $('#facultyLevelProgramTable');
        const headers = [];
        const programs = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process programs
        table.find('thead tr:last th').each(function() {
            programs.push($(this).text().trim());
        });
        
        // Combine headers and programs
        const headerRow = ['S.No.', 'Local Level'];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i > 1) { // Skip S.No. and Local Level
                if (programs[headerIndex]) {
                    headerRow.push(header + ' - ' + programs[headerIndex++]);
                }
            }
        });
        headerRow.push('Total');
        
        // Process data rows
        const rows = [headerRow];
        table.find('tbody tr:visible, tbody tr.info').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Faculty Program Dist");
        XLSX.writeFile(wb, "faculty_program_distribution.xlsx");
    });

    $('#facultyLevelProgramPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l'); // Landscape orientation
        
        doc.autoTable({
            html: '#facultyLevelProgramTable',
            styles: { 
                fontSize: 6,
                cellPadding: 2,
                overflow: 'linebreak'
            },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1,
            margin: { top: 10, right: 5, bottom: 10, left: 5 },
            pageBreak: 'auto',
            tableWidth: 'wrap'
        });
        
        doc.save('faculty_program_distribution.pdf');
    });

    $('#facultyLevelProgramPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=1000');
        printWindow.document.write('<html><head><title>Print Faculty/Program Distribution</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('<style>@page { size: landscape; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#facultyLevelProgramTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });

    // ========== Graduated Students Counts ==========
    $('#graduatedStudentsSearch').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#graduatedStudentsTable tbody tr').filter(function() {
            // Skip the grand total row from filtering
            if ($(this).find('td:first').text() === 'Grand Total') {
                return true;
            }
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#graduatedStudentsCopyBtn').click(function() {
        const table = $('#graduatedStudentsTable').clone();
        table.find('td[style*="display:none"], td[style*="display: none"]').remove();
        table.find('tr').each(function() {
            if ($(this).css('display') === 'none' && $(this).find('td:first').text() !== 'Grand Total') {
                $(this).remove();
            }
        });
        
        const $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(table.text()).select();
        document.execCommand('copy');
        $temp.remove();
        alert('Graduated students copied to clipboard');
    });

    $('#graduatedStudentsCsvBtn').click(function() {
        const table = $('#graduatedStudentsTable');
        const headers = [];
        const genders = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process genders
        table.find('thead tr:last th').each(function() {
            genders.push($(this).text().trim());
        });
        
        // Combine headers and genders
        const headerRow = ['S.No.', 'Faculty/Program'];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i > 1) { // Skip S.No. and Faculty/Program
                if (genders[headerIndex]) {
                    headerRow.push(header + ' - ' + genders[headerIndex++]);
                }
            }
        });
        headerRow.push('Total');
        
        // Process data rows
        const rows = [headerRow];
        table.find('tbody tr:visible, tfoot tr').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        let csvContent = "data:text/csv;charset=utf-8,";
        rows.forEach(function(rowArray) {
            const row = rowArray.join(",");
            csvContent += row + "\r\n";
        });
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "graduated_students.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $('#graduatedStudentsExcelBtn').click(function() {
        const table = $('#graduatedStudentsTable');
        const headers = [];
        const genders = [];
        
        // Process headers
        table.find('thead tr:first th').each(function() {
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            for (let i = 0; i < colSpan; i++) {
                headers.push(text);
            }
        });
        
        // Process genders
        table.find('thead tr:last th').each(function() {
            genders.push($(this).text().trim());
        });
        
        // Combine headers and genders
        const headerRow = ['S.No.', 'Faculty/Program'];
        let headerIndex = 0;
        headers.forEach((header, i) => {
            if (i > 1) { // Skip S.No. and Faculty/Program
                if (genders[headerIndex]) {
                    headerRow.push(header + ' - ' + genders[headerIndex++]);
                }
            }
        });
        headerRow.push('Total');
        
        // Process data rows
        const rows = [headerRow];
        table.find('tbody tr:visible, tfoot tr').each(function() {
            const rowData = [];
            $(this).find('td').each(function() {
                rowData.push($(this).text().trim());
            });
            rows.push(rowData);
        });
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, "Graduated Students");
        XLSX.writeFile(wb, "graduated_students.xlsx");
    });

    $('#graduatedStudentsPdfBtn').click(function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l'); // Landscape orientation
        
        doc.autoTable({
            html: '#graduatedStudentsTable',
            styles: { 
                fontSize: 6,
                cellPadding: 2,
                overflow: 'linebreak'
            },
            showHead: 'everyPage',
            tableLineColor: [189, 195, 199],
            tableLineWidth: 0.1,
            margin: { top: 10, right: 5, bottom: 10, left: 5 },
            pageBreak: 'auto',
            tableWidth: 'wrap'
        });
        
        doc.save('graduated_students.pdf');
    });

    $('#graduatedStudentsPrintBtn').click(function() {
        const printWindow = window.open('', '', 'height=600,width=1000');
        printWindow.document.write('<html><head><title>Print Graduated Students</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
        printWindow.document.write('<style>@page { size: landscape; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write($('#graduatedStudentsTable').parent().html());
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 200);
    });

    // ========== Province Ethnicity Gender Counts ==========
$('#provinceEthnicitySearch').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#provinceEthnicityTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

$('#provinceEthnicityCopyBtn').click(function() {
    const table = $('#provinceEthnicityTable').clone();
    table.find('td[style*="display:none"], td[style*="display: none"]').remove();
    table.find('tr').each(function() {
        if ($(this).css('display') === 'none') {
            $(this).remove();
        }
    });
    
    const $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(table.text()).select();
    document.execCommand('copy');
    $temp.remove();
    alert('Province ethnicity distribution copied to clipboard');
});

$('#provinceEthnicityCsvBtn').click(function() {
    const table = $('#provinceEthnicityTable');
    const rows = [];
    
    // Add header rows
    const headerRow1 = ['Province', 'Brahmin', '', 'Chhetri', '', 'Dalit', '', 'Janajati', '', 'Madhesi', '', 'Muslim', '', 'Tharu', '', 'Others', '', 'Total'];
    const headerRow2 = ['', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', ''];
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    // Add footer row
    const footerRow = [];
    table.find('tfoot tr td').each(function() {
        footerRow.push($(this).text().trim());
    });
    rows.push(footerRow);
    
    let csvContent = "data:text/csv;charset=utf-8,";
    rows.forEach(function(rowArray) {
        const row = rowArray.join(",");
        csvContent += row + "\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "province_ethnicity_distribution.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

$('#provinceEthnicityExcelBtn').click(function() {
    const table = $('#provinceEthnicityTable');
    const rows = [];
    
    // Add header rows
    const headerRow1 = ['Province', 'Brahmin', '', 'Chhetri', '', 'Dalit', '', 'Janajati', '', 'Madhesi', '', 'Muslim', '', 'Tharu', '', 'Others', '', 'Total'];
    const headerRow2 = ['', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', 'M', 'F', ''];
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    // Add footer row
    const footerRow = [];
    table.find('tfoot tr td').each(function() {
        footerRow.push($(this).text().trim());
    });
    rows.push(footerRow);
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Province Ethnicity Dist");
    XLSX.writeFile(wb, "province_ethnicity_distribution.xlsx");
});

$('#provinceEthnicityPdfBtn').click(function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l'); // Landscape orientation
    
    doc.autoTable({
        html: '#provinceEthnicityTable',
        styles: { 
            fontSize: 6,
            cellPadding: 2,
            overflow: 'linebreak'
        },
        showHead: 'everyPage',
        tableLineColor: [189, 195, 199],
        tableLineWidth: 0.1,
        margin: { top: 10, right: 5, bottom: 10, left: 5 },
        pageBreak: 'auto',
        tableWidth: 'wrap'
    });
    
    doc.save('province_ethnicity_distribution.pdf');
});

$('#provinceEthnicityPrintBtn').click(function() {
    const printWindow = window.open('', '', 'height=600,width=1000');
    printWindow.document.write('<html><head><title>Print Province Ethnicity Distribution</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
    printWindow.document.write('<style>@page { size: landscape; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write($('#provinceEthnicityTable').parent().html());
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 200);
});
// ========== Program Ethnicity Gender Counts ==========
$('#programEthnicitySearch').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#programEthnicityTable tbody tr').filter(function() {
        // Skip the grand total row from filtering
        if ($(this).find('td:first').text() === 'Grand Total' || $(this).find('td:first').attr('colspan')) {
            return true;
        }
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

$('#programEthnicityCopyBtn').click(function() {
    const table = $('#programEthnicityTable').clone();
    table.find('td[style*="display:none"], td[style*="display: none"]').remove();
    table.find('tr').each(function() {
        if ($(this).css('display') === 'none' && !$(this).find('td:first').attr('colspan')) {
            $(this).remove();
        }
    });
    
    const $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(table.text()).select();
    document.execCommand('copy');
    $temp.remove();
    alert('Program ethnicity distribution copied to clipboard');
});

$('#programEthnicityCsvBtn').click(function() {
    const table = $('#programEthnicityTable');
    const rows = [];
    
    // Process headers dynamically
    const headerRow1 = ['S.No.', 'Program Name'];
    const headerRow2 = ['', ''];
    
    table.find('thead tr:first th').each(function(index) {
        if (index > 1) { // Skip S.No. and Program Name
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            if (colSpan == 2) {
                headerRow1.push(text, '');
                headerRow2.push('Male', 'Female');
            } else {
                headerRow1.push(text);
                headerRow2.push('');
            }
        }
    });
    
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    let csvContent = "data:text/csv;charset=utf-8,";
    rows.forEach(function(rowArray) {
        const row = rowArray.join(",");
        csvContent += row + "\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "program_ethnicity_distribution.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

$('#programEthnicityExcelBtn').click(function() {
    const table = $('#programEthnicityTable');
    const rows = [];
    
    // Process headers dynamically
    const headerRow1 = ['S.No.', 'Program Name'];
    const headerRow2 = ['', ''];
    
    table.find('thead tr:first th').each(function(index) {
        if (index > 1) { // Skip S.No. and Program Name
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            if (colSpan == 2) {
                headerRow1.push(text, '');
                headerRow2.push('Male', 'Female');
            } else {
                headerRow1.push(text);
                headerRow2.push('');
            }
        }
    });
    
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Program Ethnicity Dist");
    XLSX.writeFile(wb, "program_ethnicity_distribution.xlsx");
});

$('#programEthnicityPdfBtn').click(function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l'); // Landscape orientation
    
    doc.autoTable({
        html: '#programEthnicityTable',
        styles: { 
            fontSize: 6,
            cellPadding: 2,
            overflow: 'linebreak'
        },
        showHead: 'everyPage',
        tableLineColor: [189, 195, 199],
        tableLineWidth: 0.1,
        margin: { top: 10, right: 5, bottom: 10, left: 5 },
        pageBreak: 'auto',
        tableWidth: 'wrap'
    });
    
    doc.save('program_ethnicity_distribution.pdf');
});

$('#programEthnicityPrintBtn').click(function() {
    const printWindow = window.open('', '', 'height=600,width=1000');
    printWindow.document.write('<html><head><title>Print Program Ethnicity Distribution</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
    printWindow.document.write('<style>@page { size: landscape; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write($('#programEthnicityTable').parent().html());
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 200);
});

// ========== District Ethnicity Gender Counts ==========
$('#districtEthnicitySearch').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#districtEthnicityTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

$('#districtEthnicityCopyBtn').click(function() {
    const table = $('#districtEthnicityTable').clone();
    table.find('td[style*="display:none"], td[style*="display: none"]').remove();
    table.find('tr').each(function() {
        if ($(this).css('display') === 'none') {
            $(this).remove();
        }
    });
    
    const $temp = $('<textarea>');
    $('body').append($temp);
    $temp.val(table.text()).select();
    document.execCommand('copy');
    $temp.remove();
    alert('District ethnicity distribution copied to clipboard');
});

$('#districtEthnicityCsvBtn').click(function() {
    const table = $('#districtEthnicityTable');
    const rows = [];
    
    // Process headers dynamically
    const headerRow1 = ['District'];
    const headerRow2 = [''];
    
    table.find('thead tr:first th').each(function(index) {
        if (index > 0) { // Skip District
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            if (colSpan == 2) {
                headerRow1.push(text, '');
                headerRow2.push('M', 'F');
            } else {
                headerRow1.push(text);
                headerRow2.push('');
            }
        }
    });
    
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    // Add footer row
    const footerRow = [];
    table.find('tfoot tr th').each(function() {
        footerRow.push($(this).text().trim());
    });
    rows.push(footerRow);
    
    let csvContent = "data:text/csv;charset=utf-8,";
    rows.forEach(function(rowArray) {
        const row = rowArray.join(",");
        csvContent += row + "\r\n";
    });
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "district_ethnicity_distribution.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

$('#districtEthnicityExcelBtn').click(function() {
    const table = $('#districtEthnicityTable');
    const rows = [];
    
    // Process headers dynamically
    const headerRow1 = ['District'];
    const headerRow2 = [''];
    
    table.find('thead tr:first th').each(function(index) {
        if (index > 0) { // Skip District
            const colSpan = $(this).attr('colspan') || 1;
            const text = $(this).text().trim();
            if (colSpan == 2) {
                headerRow1.push(text, '');
                headerRow2.push('M', 'F');
            } else {
                headerRow1.push(text);
                headerRow2.push('');
            }
        }
    });
    
    rows.push(headerRow1);
    rows.push(headerRow2);
    
    // Add data rows (visible only)
    table.find('tbody tr:visible').each(function() {
        const rowData = [];
        $(this).find('td').each(function() {
            rowData.push($(this).text().trim());
        });
        rows.push(rowData);
    });
    
    // Add footer row
    const footerRow = [];
    table.find('tfoot tr th').each(function() {
        footerRow.push($(this).text().trim());
    });
    rows.push(footerRow);
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "District Ethnicity Dist");
    XLSX.writeFile(wb, "district_ethnicity_distribution.xlsx");
});

$('#districtEthnicityPdfBtn').click(function() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l'); // Landscape orientation
    
    doc.autoTable({
        html: '#districtEthnicityTable',
        styles: { 
            fontSize: 6,
            cellPadding: 2,
            overflow: 'linebreak'
        },
        showHead: 'everyPage',
        tableLineColor: [189, 195, 199],
        tableLineWidth: 0.1,
        margin: { top: 10, right: 5, bottom: 10, left: 5 },
        pageBreak: 'auto',
        tableWidth: 'wrap'
    });
    
    doc.save('district_ethnicity_distribution.pdf');
});

$('#districtEthnicityPrintBtn').click(function() {
    const printWindow = window.open('', '', 'height=600,width=1000');
    printWindow.document.write('<html><head><title>Print District Ethnicity Distribution</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
    printWindow.document.write('<style>@page { size: landscape; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write($('#districtEthnicityTable').parent().html());
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 200);
});
});


</script>


