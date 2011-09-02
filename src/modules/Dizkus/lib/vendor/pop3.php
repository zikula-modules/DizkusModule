<?php
/*
 * pop3.php
 *
 * @(#) $Header$
 *
 */

class pop3_class
{
	var $hostname="";
	var $port=110;
	var $quit_handshake=0;
	var $error="";
	var $authentication_mechanism="USER";
	var $realm="";
	var $workstation="";
	var $join_continuation_header_lines=1;

	/* Private variables - DO NOT ACCESS */

	var $connection=0;
	var $state="DISCONNECTED";
	var $greeting="";
	var $must_update=0;
	var $debug=0;
	var $html_debug=0;
	var $next_token="";

	/* Private methods - DO NOT CALL */

	Function Tokenize($string,$separator="")
	{
		if(!strcmp($separator,""))
		{
			$separator=$string;
			$string=$this->next_token;
		}
		for($character=0;$character<strlen($separator);$character++)
		{
			if(GetType($position=strpos($string,$separator[$character]))=="integer")
				$found=(IsSet($found) ? min($found,$position) : $position);
		}
		if(IsSet($found))
		{
			$this->next_token=substr($string,$found+1);
			return(substr($string,0,$found));
		}
		else
		{
			$this->next_token="";
			return($string);
		}
	}

	Function SetError($error)
	{
		return($this->error=$error);
	}

	Function OutputDebug($message)
	{
		$message.="\n";
		if($this->html_debug)
			$message=str_replace("\n","<br />\n",HtmlEntities($message));
		echo $message;
		flush();
	}

	Function GetLine()
	{
		for($line="";;)
		{
			if(feof($this->connection))
				return(0);
			$line.=fgets($this->connection,100);
			$length=strlen($line);
			if($length>=2
			&& substr($line,$length-2,2)=="\r\n")
			{
				$line=substr($line,0,$length-2);
				if($this->debug)
					$this->OutputDebug("S $line");
				return($line);
			}
		}
	}

	Function PutLine($line)
	{
		if($this->debug)
			$this->OutputDebug("C $line");
		return(fputs($this->connection,"$line\r\n"));
	}

	Function OpenConnection()
	{
		if($this->hostname=="")
			return($this->SetError("2 Invalid host name specified."));
		if($this->debug)
			$this->OutputDebug("Connecting to ".$this->hostname." ...");
		if(($this->connection=@fsockopen($this->hostname,$this->port,$error))==0)
		{
			switch($error)
			{
				case -3:
					return($this->SetError("-3 Could not create socket."));
				case -4:
					return($this->SetError("-4 DNS look-up on host name ''$hostname' failed."));
				case -5:
					return($this->SetError("-5 Connection refused or timed out."));
				case -6:
					return($this->SetError("-6 fdopen() call failed."));
				case -7:
					return($this->SetError("-7 setvbuf() call failed."));
				default:
					return($this->SetError($error." Could not connect to host \"".$this->hostname."\""));
			}
		}
		return("");
	}

	Function CloseConnection()
	{
		if($this->debug)
			$this->OutputDebug("Closing connection.");
		if($this->connection!=0)
		{
			fclose($this->connection);
			$this->connection=0;
		}
	}

	/* Public methods */

	/* Open method - set the object variable $hostname to the POP3 server address. */

	Function Open()
	{
		if($this->state!="DISCONNECTED")
			return($this->SetError("1 There is already a session open."));
		if(($error=$this->OpenConnection())!="")
			return($error);
		$this->greeting=$this->GetLine();
		if(GetType($this->greeting)!="string"
		|| $this->Tokenize($this->greeting," ")!="+OK")
		{
			$this->CloseConnection();
			return($this->SetError("3 POP3 server greeting not found."));
		}
		$this->Tokenize("<");
		$this->must_update=0;
		$this->state="AUTHORIZATION";
		return("");
	}

	/* Close method - this method must be called at least if there are any
     messages to be deleted */

	Function Close()
	{
		if($this->state=="DISCONNECTED")
			return($this->SetError("No session opened."));
		if($this->must_update
		|| $this->quit_handshake)
		{
			if($this->PutLine("QUIT")==0)
				return($this->SetError("Could not send QUIT command."));
			$response=$this->GetLine();
			if(GetType($response)!="string")
				return($this->SetError("Could not get quit command response."));
			if($this->Tokenize($response," ")!="+OK")
				return($this->SetError("Could not exit session: ".$this->Tokenize("\r\n")));
		}
		$this->CloseConnection();
		$this->state="DISCONNECTED";
		return("");
	}

	/* Login method - pass the user name and password of POP account.  Set
     $apop to 1 or 0 wether you want to login using APOP method or not.  */

	Function Login($user,$password,$apop=0)
	{
		if($this->state!="AUTHORIZATION")
			return($this->SetError("Session is not in AUTHORIZATION state."));
		if($apop)
		{
			if(!strcmp($this->greeting,""))
				return($this->SetError("Server does not seem to support APOP authentication."));
			if($this->PutLine("APOP $user ".md5("<".$this->greeting.">".$password))==0)
				return($this->SetError("Could not send APOP command."));
			$response=$this->GetLine();
			if(GetType($response)!="string")
				return($this->SetError("Could not get APOP log-in command response."));
			if($this->Tokenize($response," ")!="+OK")
				return($this->SetError("APOP log-in failed: ".$this->Tokenize("\r\n")));
		}
		else
		{
			$authenticated=0;
			if(strcmp($this->authentication_mechanism,"USER")
			&& function_exists("Class_exists.")
			&& class_exists("sasl_client_class"))
			{
				if(strlen($this->authentication_mechanism))
					$mechanisms=array($this->authentication_mechanism);
				else
				{
					$mechanisms=array();
					if($this->PutLine("CAPA")==0)
						return($this->SetError("Could not send CAPA command."));
					$response=$this->GetLine();
					if(GetType($response)!="string")
						return($this->SetError("Could not get CAPA command response."));
					if(!strcmp($this->Tokenize($response," "),"+OK"))
					{
						for(;;)
						{
							$response=$this->GetLine();
							if(GetType($response)!="string")
								return($this->SetError("Could not retrieve supported authentication methods."));
							switch($this->Tokenize($response," "))
							{
								case ".":
									break 2;
								case "SASL":
									for($method=1;strlen($mechanism=$this->Tokenize(" "));$method++)
										$mechanisms[]=$mechanism;
									break;
							}
						}
					}
				}
				$sasl=new sasl_client_class;
				$sasl->SetCredential("user",$user);
				$sasl->SetCredential("password",$password);
				if(strlen($this->realm))
					$sasl->SetCredential("realm",$this->realm);
				if(strlen($this->workstation))
					$sasl->SetCredential("workstation",$this->workstation);
				do
				{
					$status=$sasl->Start($mechanisms,$message,$interactions);
				}
				while($status==SASL_INTERACT);
				switch($status)
				{
					case SASL_CONTINUE:
						break;
					case SASL_NOMECH:
						if(strlen($this->authentication_mechanism))
							return($this->SetError("Authentication mechanism ".$this->authentication_mechanism." cannot be used: ".$sasl->error));
						break;
					default:
						return($this->SetError("Could not start SASL authentication client: ".$sasl->error));
				}
				if(strlen($sasl->mechanism))
				{
					if($this->PutLine("AUTH ".$sasl->mechanism.(IsSet($message) ? " ".base64_encode($message) : ""))==0)
						return("Could not send AUTH command.");
					$response=$this->GetLine();
					if(GetType($response)!="string")
						return("Could not get AUTH command response.");
					switch($this->Tokenize($response," "))
					{
						case "+OK":
							$response="";
							break;
						case "+":
							$response=base64_decode($this->Tokenize("\r\n"));
							break;
						default:
							return($this->SetError("Authentication error: ".$this->Tokenize("\r\n")));
					}
					for(;!$authenticated;)
					{
						do
						{
							$status=$sasl->Step($response,$message,$interactions);
						}
						while($status==SASL_INTERACT);
						switch($status)
						{
							case SASL_CONTINUE:
								if($this->PutLine(base64_encode($message))==0)
									return("Could not send message authentication step message.");
								$response=$this->GetLine();
								if(GetType($response)!="string")
									return("Could not get authentication step message response.");
								switch($this->Tokenize($response," "))
								{
									case "+OK":
										$authenticated=1;
										break;
									case "+":
										$response=base64_decode($this->Tokenize("\r\n"));
										break;
									default:
										return($this->SetError("Authentication error: ".$this->Tokenize("\r\n")));
								}
								break;
							default:
								return($this->SetError("Could not process SASL authentication step: ".$sasl->error));
						}
					}
				}
			}
			if(!$authenticated)
			{
				if($this->PutLine("USER $user")==0)
					return($this->SetError("Could not send USER command."));
				$response=$this->GetLine();
				if(GetType($response)!="string")
					return($this->SetError("Could not get user log-in entry response."));
				if($this->Tokenize($response," ")!="+OK")
					return($this->SetError("User error: ".$this->Tokenize("\r\n")));
				if($this->PutLine("PASS $password")==0)
					return($this->SetError("Could not send PASS command."));
				$response=$this->GetLine();
				if(GetType($response)!="string")
					return($this->SetError("Could not get log-in password entry response."));
				if($this->Tokenize($response," ")!="+OK")
					return($this->SetError("Password error: ".$this->Tokenize("\r\n")));
			}
		}
		$this->state="TRANSACTION";
		return("");
	}

	/* Statistics method - pass references to variables to hold the number of
     messages in the mail box and the size that they take in bytes.  */

	Function Statistics(&$messages,&$size)
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($this->PutLine("STAT")==0)
			return($this->SetError("Could not send STAT command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get statistics command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not get statistics: ".$this->Tokenize("\r\n")));
		$messages=$this->Tokenize(" ");
		$size=$this->Tokenize(" ");
		return("");
	}

	/* ListMessages method - the $message argument indicates the number of a
     message to be listed.  If you specify an empty string it will list all
     messages in the mail box.  The $unique_id flag indicates if you want
     to list the each message unique identifier, otherwise it will
     return the size of each message listed.  If you list all messages the
     result will be returned in an array. */

	Function ListMessages($message,$unique_id)
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($unique_id)
			$list_command="UIDL";
		else
			$list_command="LIST";
		if($this->PutLine("$list_command".($message ? " ".$message : ""))==0)
			return($this->SetError("Could not send $list_command command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get message list command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not get message listing: ".$this->Tokenize("\r\n")));
		if($message=="")
		{
			for($messages=array();;)
			{
				$response=$this->GetLine();
				if(GetType($response)!="string")
					return($this->SetError("Could not get message list response."));
				if($response==".")
					break;
				$message=intval($this->Tokenize($response," "));
				if($unique_id)
					$messages[$message]=$this->Tokenize(" ");
				else
					$messages[$message]=intval($this->Tokenize(" "));
			}
			return($messages);
		}
		else
		{
			$message=intval($this->Tokenize(" "));
			$value=$this->Tokenize(" ");
			return($unique_id ? $value : intval($value));
		}
	}

	/* RetrieveMessage method - the $message argument indicates the number of
     a message to be listed.  Pass a reference variables that will hold the
     arrays of the $header and $body lines.  The $lines argument tells how
     many lines of the message are to be retrieved.  Pass a negative number
     if you want to retrieve the whole message. */

	Function RetrieveMessage($message,&$headers,&$body,$lines)
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($lines<0)
		{
			$command="RETR";
			$arguments="$message";
		}
		else
		{
			$command="TOP";
			$arguments="$message $lines";
		}
		if($this->PutLine("$command $arguments")==0)
			return($this->SetError("Could not send $command command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get message retrieval command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not retrieve message: ".$this->Tokenize("\r\n")));
		for($headers=$body=array(),$line=0;;)
		{
			$response=$this->GetLine();
			if(GetType($response)!="string")
				return($this->SetError("Could not retrieve message."));
			switch($response)
			{
				case ".":
					return("");
				case "":
					break 2;
				default:
					if(substr($response,0,1)==".")
						$response=substr($response,1,strlen($response)-1);
					break;
			}
			if($this->join_continuation_header_lines
			&& $line>0
			&& ($response[0]=="\t"
			|| $response[0]==" "))
				$headers[$line-1].=$response;
			else
			{
				$headers[$line]=$response;
				$line++;
			}
		}
		for($line=0;;$line++)
		{
			$response=$this->GetLine();
			if(GetType($response)!="string")
				return($this->SetError("Could not retrieve message."));
			switch($response)
			{
				case ".":
					return("");
				default:
					if(substr($response,0,1)==".")
						$response=substr($response,1,strlen($response)-1);
					break;
			}
			$body[$line]=utf8_encode($response);
		}
		return("");
	}

	/* DeleteMessage method - the $message argument indicates the number of
     a message to be marked as deleted.  Messages will only be effectively
     deleted upon a successful call to the Close method. */

	Function DeleteMessage($message)
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($this->PutLine("DELE $message")==0)
			return($this->SetError("Could not send DELE command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get message delete command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not delete message: ".$this->Tokenize("\r\n")));
		$this->must_update=1;
		return("");
	}

	/* ResetDeletedMessages method - Reset the list of marked to be deleted
     messages.  No messages will be marked to be deleted upon a successful
     call to this method.  */

	Function ResetDeletedMessages()
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($this->PutLine("RSET")==0)
			return($this->SetError("Could not send RSET command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get reset deleted messages command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not reset deleted messages: ".$this->Tokenize("\r\n")));
		$this->must_update=0;
		return("");
	}

	/* IssueNOOP method - Just pings the server to prevent it auto-close the
     connection after an idle timeout (tipically 10 minutes).  Not very
     useful for most likely uses of this class.  It's just here for
     protocol support completeness.  */

	Function IssueNOOP()
	{
		if($this->state!="TRANSACTION")
			return($this->SetError("Session is not in TRANSACTION state."));
		if($this->PutLine("NOOP")==0)
			return($this->SetError("Could not send NOOP command."));
		$response=$this->GetLine();
		if(GetType($response)!="string")
			return($this->SetError("Could not get NOOP command response."));
		if($this->Tokenize($response," ")!="+OK")
			return($this->SetError("Could not issue NOOP command: ".$this->Tokenize("\r\n")));
		return("");
	}
};

?>