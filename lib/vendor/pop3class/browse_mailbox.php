<?php
/*
 * browse_mailbox.php
 *
 * @(#) $Header: /home/mlemos/cvsroot/pop3/browse_mailbox.php,v 1.1 2008/01/09 07:36:25 mlemos Exp $
 *
 */

?><html>
<head>
<title>Parsing a message with Manuel Lemos' PHP POP3 and MIME Parser classes</title>
</head>
<body>
<center><h1>Parsing a message with Manuel Lemos' PHP POP3 and MIME Parser classes</h1></center>
<hr />
<?php

	require('mime_parser.php');
	require('rfc822_addresses.php');
	require("pop3.php");

  /* Uncomment when using SASL authentication mechanisms */
	/*
	require("sasl.php");
	*/

	stream_wrapper_register('pop3', 'pop3_stream');  /* Register the pop3 stream handler class */

	$pop3=new pop3_class;
	$pop3->hostname="localhost";             /* POP 3 server host name                      */
	$pop3->port=110;                         /* POP 3 server host port,
	                                            usually 110 but some servers use other ports
	                                            Gmail uses 995                              */
	$pop3->tls=0;                            /* Establish secure connections using TLS      */
	$user="username";                        /* Authentication user name                    */
	$password="password";                    /* Authentication password                     */
	$pop3->realm="";                         /* Authentication realm or domain              */
	$pop3->workstation="";                   /* Workstation for NTLM authentication         */
	$apop=0;                                 /* Use APOP authentication                     */
	$pop3->authentication_mechanism="USER";  /* SASL authentication mechanism               */
	$pop3->debug=1;                          /* Output debug information                    */
	$pop3->html_debug=1;                     /* Debug information is in HTML                */
	$pop3->join_continuation_header_lines=1; /* Concatenate headers split in multiple lines */

	if(($error=$pop3->Open())=="")
	{
		echo "<PRE>Connected to the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
		if(($error=$pop3->Login($user,$password,$apop))=="")
		{
			echo "<PRE>User &quot;$user&quot; logged in.</PRE>\n";
			if(($error=$pop3->Statistics($messages,$size))=="")
			{
				echo "<PRE>There are $messages messages in the mail box with a total of $size bytes.</PRE>\n";
				if($messages>0)
				{
					$pop3->GetConnectionName($connection_name);
					$message=1;
					$message_file='pop3://'.$connection_name.'/'.$message;
					$mime=new mime_parser_class;

					/*
					* Set to 0 for not decoding the message bodies
					*/
					$mime->decode_bodies = 1;

					$parameters=array(
						'File'=>$message_file,

						/* Read a message from a string instead of a file */
						/* 'Data'=>'My message data string',              */

						/* Save the message body parts to a directory     */
						/* 'SaveBody'=>'/tmp',                            */

						/* Do not retrieve or save message body parts     */
							'SkipBody'=>1,
					);
					$success=$mime->Decode($parameters, $decoded);


					if(!$success)
						echo '<h2>MIME message decoding error: '.HtmlSpecialChars($mime->error)."</h2>\n";
					else
					{
						echo '<h2>MIME message decoding successful</h2>'."\n";
						echo '<h2>Message structure</h2>'."\n";
						echo '<pre>';
						var_dump($decoded[0]);
						echo '</pre>';
						if($mime->Analyze($decoded[0], $results))
						{
							echo '<h2>Message analysis</h2>'."\n";
							echo '<pre>';
							var_dump($results);
							echo '</pre>';
						}
						else
							echo 'MIME message analyse error: '.$mime->error."\n";
					}
				}
				if($error==""
				&& ($error=$pop3->Close())=="")
					echo "<PRE>Disconnected from the POP3 server &quot;".$pop3->hostname."&quot;.</PRE>\n";
			}
		}
	}
	if($error!="")
		echo "<H2>Error: ",HtmlSpecialChars($error),"</H2>";
?>

</body>
</html>
