<?php

$email = trim( "MyEmailAddress@example.com " ); // "MyEmailAddress@example.com"
$email = strtolower( $email ); // "myemailaddress@example.com"
echo md5( $email );
// "0bc83cb571cd1c50ba6f3e8a78ef1346"

?>