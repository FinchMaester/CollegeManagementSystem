import re
import sys

def extract_table_name(line):
    """Extract table name from CREATE TABLE statement"""
    match = re.search(r'CREATE TABLE\s+`?(\w+)`?', line, re.IGNORECASE)
    return match.group(1) if match else None

def extract_table_structure(sql_file):
    """Extract all CREATE TABLE statements with their full structure"""
    tables = {}
    current_table = None
    current_structure = []
    in_create_table = False
    
    with open(sql_file, 'r', encoding='utf-8', errors='ignore') as f:
        for line in f:
            line_stripped = line.strip()
            
            # Check if this is a CREATE TABLE statement
            if re.match(r'CREATE TABLE', line_stripped, re.IGNORECASE):
                # Save previous table if exists
                if current_table:
                    tables[current_table] = '\n'.join(current_structure)
                
                # Start new table
                table_name = extract_table_name(line_stripped)
                if table_name:
                    current_table = table_name
                    current_structure = [line_stripped]
                    in_create_table = True
            elif in_create_table:
                current_structure.append(line_stripped)
                # Check if this is the end of CREATE TABLE (ENGINE= or ;)
                if line_stripped.endswith(';') or (line_stripped.startswith('ENGINE=') and ';' in line_stripped):
                    if current_table:
                        tables[current_table] = '\n'.join(current_structure)
                    current_table = None
                    current_structure = []
                    in_create_table = False
    
    # Save last table if exists
    if current_table:
        tables[current_table] = '\n'.join(current_structure)
    
    return tables

def extract_columns(create_statement):
    """Extract column definitions from CREATE TABLE statement"""
    columns = {}
    lines = create_statement.split('\n')
    
    for line in lines:
        line = line.strip()
        # Match column definitions (lines that start with `column_name`)
        match = re.match(r'`(\w+)`\s+(.+?)(?:,|$)', line)
        if match:
            col_name = match.group(1)
            col_def = match.group(2).strip().rstrip(',')
            columns[col_name] = col_def
    
    return columns

def main():
    file1 = r'c:\Users\Lenovo\Downloads\collegesystem.sql'
    file2 = r'c:\Users\Lenovo\Downloads\u129910324_kvmcollege.sql'
    
    print("Extracting tables from collegesystem.sql...")
    tables1 = extract_table_structure(file1)
    
    print("Extracting tables from u129910324_kvmcollege.sql...")
    tables2 = extract_table_structure(file2)
    
    print(f"\nFound {len(tables1)} tables in collegesystem.sql")
    print(f"Found {len(tables2)} tables in u129910324_kvmcollege.sql\n")
    
    # Find missing tables
    missing_tables = set(tables1.keys()) - set(tables2.keys())
    
    print("=" * 80)
    print("MISSING TABLES (in server database)")
    print("=" * 80)
    
    sql_queries = []
    
    if missing_tables:
        for table_name in sorted(missing_tables):
            print(f"\nTable: {table_name}")
            create_stmt = tables1[table_name]
            # Clean up the statement - remove trailing commas before ENGINE
            create_stmt_clean = re.sub(r',\s*ENGINE=', ' ENGINE=', create_stmt, flags=re.IGNORECASE)
            sql_queries.append(create_stmt_clean + ';')
            print(create_stmt_clean + ';')
    else:
        print("No missing tables found.")
    
    print("\n" + "=" * 80)
    print("MISSING COLUMNS (in existing tables)")
    print("=" * 80)
    
    # Find missing columns in existing tables
    common_tables = set(tables1.keys()) & set(tables2.keys())
    
    for table_name in sorted(common_tables):
        cols1 = extract_columns(tables1[table_name])
        cols2 = extract_columns(tables2[table_name])
        
        missing_cols = set(cols1.keys()) - set(cols2.keys())
        
        if missing_cols:
            print(f"\nTable: {table_name}")
            for col_name in sorted(missing_cols):
                col_def = cols1[col_name]
                # Determine position - try to find after which column
                col_list = list(cols1.keys())
                col_index = col_list.index(col_name)
                
                if col_index > 0:
                    prev_col = col_list[col_index - 1]
                    alter_query = f"ALTER TABLE `{table_name}` ADD COLUMN `{col_name}` {col_def} AFTER `{prev_col}`;"
                else:
                    alter_query = f"ALTER TABLE `{table_name}` ADD COLUMN `{col_name}` {col_def} FIRST;"
                
                sql_queries.append(alter_query)
                print(alter_query)
    
    print("\n" + "=" * 80)
    print(f"\nTotal SQL queries generated: {len(sql_queries)}")
    print("=" * 80)

if __name__ == '__main__':
    main()













