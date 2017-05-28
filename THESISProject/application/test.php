<?php 
$serverName = 'DESKTOP-AO5RI8M';   
$uid = 'sa';     
$pwd = 'password';    
$databaseName = 'TS-VCS-TEST';
$connectionInfo = array( "UID"=>$uid, "PWD"=>$pwd, "Database"=>$databaseName); 

/* Connect using SQL Server Authentication. */    
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$sqlStr = "select TABLE_NAME, COLUMN_NAME, COLUMN_DEFAULT, IS_NULLABLE 
	from INFORMATION_SCHEMA.COLUMNS
	where COLUMN_NAME = 'username' and TABLE_NAME = 'M_USERS'";
$stmt = sqlsrv_query( $conn, $sqlStr);    
if ( $stmt )    
{    
     echo "Statement executed.<br>\n";
    $sqlStr = "EXEC getCheckConstraint @constraintName = 'ChkGrade_field'";
	$result = sqlsrv_query($conn, $sqlStr);
	echo $result;    
}     
else     
{    
     echo "Error in statement execution.\n";    
     die( print_r( sqlsrv_errors(), true));    
} 

/* Free statement and connection resources. */    
sqlsrv_free_stmt( $stmt);    
sqlsrv_close( $conn); 
?>