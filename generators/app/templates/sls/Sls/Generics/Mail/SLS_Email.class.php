<?php
/**
 * Send mail from a distant SMTP
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Mail 
 * @since 1.0
 */
class SLS_Email 
{
	var $headers		= array(
    	'MIME-version'	=> "1.0",		
		'Return-Path'	=> "",		
		'Date'			=> "",
		'From'			=> "",
		'Subject'		=> "",
		'To'			=> array(),
		'Cc'			=> array(),
		'Bcc'			=> array(),
		'X-Mailer'		=> "",
		'Content-type'	=> "",
	);
	var $message		= "This is a MIME encoded message.\r\n\r\n";
	var $charset		= "UTF-8";
	var $boundary		= array();
	var $filetypes		= array(
		'gif'	=> "image/gif",
		'jpg'	=> "image/jpeg",
		'jpe'	=> "image/jpeg",
		'jpeg'	=> "image/jpeg",
		'png'	=> "image/png",
		'bmp'	=> "image/bmp",
		'tif'	=> "image/tiff",
		'tiff'	=> "image/tiff",
		'swf'	=> "application/x-shockwave-flash",
		'wav'	=> "audio/wav",
	);
	var $attachtypes = array(
		'hqx'	=> "application/macbinhex40",
		'pdf'	=> "application/pdf",
		'pgp'	=> "application/pgp",
		'ps'	=> "application/postscript",
		'eps'	=> "application/postscript",
		'ai'	=> "application/postscript",
		'rtf'	=> "application/rtf",
		'xls'	=> "application/vnd.ms-excel",
		'pps'	=> "application/vnd.ms-powerpoint",
		'ppt'	=> "application/vnd.ms-powerpoint",
		'ppz'	=> "application/vnd.ms-powerpoint",
		'doc'	=> "application/vnd.ms-word",
		'dot'	=> "application/vnd.ms-word",
		'wrd'	=> "application/vnd.ms-word",
		'tgz'	=> "application/x-gtar",
		'gtar'	=> "application/x-gtar",
		'gz'	=> "application/x-gzip",
		'php'	=> "application/x-httpd-php",
		'php3'	=> "application/x-httpd-php",
		'js'	=> "application/x-javascript",
		'msi'	=> "application/x-msi",
		'swf'	=> "application/x-shockwave-flash",
		'rf'	=> "application/x-shockwave-flash",
		'tar'	=> "application/x-tar",
		'zip'	=> "application/zip",
		'au'	=> "audio/basic",
		'mid'	=> "audio/midi",
		'midi'	=> "audio/midi",
		'kar'	=> "audio/midi",
		'mp2'	=> "audio/mpeg",
		'mp3'	=> "audio/mpeg",
		'mpga'	=> "audio/mpeg",
		'voc'	=> "audio/voc",
		'vox'	=> "audio/voxware",
		'aif'	=> "audio/x-aiff",
		'aiff'	=> "audio/x-aiff",
		'aifc'	=> "audio/x-aiff",
		'wma'	=> "audio/x-ms-wma",
		'ra'	=> "audio/x-pn-realaudio",
		'ram'	=> "audio/x-pn-realaudio",
		'rm'	=> "audio/x-pn-realaudio",
		'ogg'	=> "audio/x-vorbis",
		'wav'	=> "audio/wav",
		'bmp'	=> "image/bmp",
		'dib'	=> "image/bmp",
		'gif'	=> "image/gif",
		'jpg'	=> "image/jpeg",
		'jpe'	=> "image/jpeg",
		'jpeg'	=> "image/jpeg",
		'jfif'	=> "image/jpeg",
		'pcx'	=> "image/pcx",
		'png'	=> "image/png",
		'tif'	=> "image/tiff",
		'tiff'	=> "image/tiff",
		'ico'	=> "image/x-icon",
		'pct'	=> "image/x-pict",
		'txt'	=> "text/plain",
		'htm'	=> "text/html",
		'html'	=> "text/html",
		'xml'	=> "text/xml",
		'xsl'	=> "text/xml",
		'dtd'	=> "text/xml-dtd",
		'css'	=> "text/css",
		'c'		=> "text/x-c",
		'c++'	=> "text/x-c",
		'cc'	=> "text/x-c",
		'cpp'	=> "text/x-c",
		'cxx'	=> "text/x-c",
		'h'		=> "text/x-h",
		'h++'	=> "text/x-h",
		'hh'	=> "text/x-h",
		'hpp'	=> "text/x-h",
		'mpg'	=> "video/mpeg",
		'mpe'	=> "video/mpeg",
		'mpeg'	=> "video/mpeg",
		'qt'	=> "video/quicktime",
		'mov'	=> "video/quicktime",
		'avi'	=> "video/x-ms-video",
		'wm'	=> "video/x-ms-wm",
		'wmv'	=> "video/x-ms-wmv",
		'wmx'	=> "video/x-ms-wmx",
		''		=> "application/octet-stream",
	);
	var $versionhtml	= "";
	var $versionplain	= "";
	var $parts			= array();
	var $files			= array();
	var $attachments	= array();
	var $hostname		= "";
	var $hostaddr		= "";
	var $smtpport;
	var $responseserver = "";
	var $login;
	var $password;
	var $logurl 		= "";
	var $error 			= array();
	var $server;
	var $compiled 		= false;

	/**
	 * Constructor
	 *	 
	 * @access public
	 * @param string $hostname smtp host (leave empty for simple mail() function)
	 * @param int $smtpport smtp port (leave empty for simple mail() function)
	 * @param string $login smtp login (leave empty for anonymous authentication or simple mail() function)
	 * @param string $password smtp password (leave empty for anonymous authentication or simple mail() function)
	 * @since 1.0
	 */
	public function __construct($hostname="",$smtpport="",$login="",$password="")
	{
		if (empty($hostname) && empty($smtpport) && empty($login) && empty($password))
		{
			$hostname 	= SLS_Security::getInstance()->decrypt(SLS_Generic::getInstance()->getMailConfig("host"),SLS_Generic::getInstance()->getSiteConfig("privateKey"));
			$smtpport 	= SLS_Security::getInstance()->decrypt(SLS_Generic::getInstance()->getMailConfig("port"),SLS_Generic::getInstance()->getSiteConfig("privateKey"));
			$login 		= SLS_Security::getInstance()->decrypt(SLS_Generic::getInstance()->getMailConfig("username"),SLS_Generic::getInstance()->getSiteConfig("privateKey"));
			$password 	= SLS_Security::getInstance()->decrypt(SLS_Generic::getInstance()->getMailConfig("password"),SLS_Generic::getInstance()->getSiteConfig("privateKey"));
		}
		
		$this->hostname 				= $hostname;
		$this->hostaddr 				= gethostbyname($hostname);
		$this->smtpport 				= $smtpport;
		$this->login 					= $login;
		$this->password 				= $password;
		$this->logurl 					= SLS_Generic::getInstance()->getPathConfig("logs")."mail.log";
		$this->headers['Date'] 			= date("D, d M Y H:i:s O",time());
		$this->boundary['mixed'] 		= md5(uniqid(microtime()));
		$this->boundary['related'] 		= md5(uniqid(microtime()));
		$this->boundary['alternative'] 	= md5(uniqid(microtime()));
		
		if (!file_exists($this->logurl))
			@touch($this->logurl);
	}

	/**
	 * Compile plain txt part
	 * 
	 * @access public
	 * @param array $matchesA <a href
	 * @return string txt updated
	 * @since 1.0.9
	 */
	public function compilePlain($matchesA)
	{
		$matchesImg = array();
		if(preg_match('/alt="([^"]*)"/i', $matchesA[4], $matchesImg))
			$label = $matchesImg[1];
		else
			$label = $matchesA[4];
		return SLS_String::startsWith($label, 'http') ? $matchesA[2] : $label.' ('.$matchesA[2].')';
	}
	
	/**
	 * Compile the mail before sending it
	 *	
	 * @access public
	 * @since 1.0
	 */
	public function compileMail() 
	{
		if (empty($this->headers['From']))
  			$this->setSender(SLS_Generic::getInstance()->getMailConfig('defaultSender').'@'.SLS_Generic::getInstance()->getMailConfig('defaultDomain'), SLS_Generic::getInstance()->getMailConfig('defaultNameSender'));
  		if (empty($this->headers['Reply-To']))
  			$this->setReply(SLS_Generic::getInstance()->getMailConfig('defaultReply').'@'.SLS_Generic::getInstance()->getMailConfig('defaultDomain'), SLS_Generic::getInstance()->getMailConfig('defaultNameReply'));
  		if (empty($this->headers['Return-Path']))
  			$this->setReply(SLS_Generic::getInstance()->getMailConfig('defaultReturn').'@'.SLS_Generic::getInstance()->getMailConfig('defaultDomain'), SLS_Generic::getInstance()->getMailConfig('defaultNameReturn'));
		
		if((empty($this->headers['To']) && empty($this->headers['Cc']) && empty($this->headers['Bcc'])) || (empty($this->headers['From']) && empty($this->headers['Return-Path'])))
			return $this->error("Some required headers are missing.");

		if($this->versionplain == "" && $this->versionhtml != "")
			$this->versionplain = strip_tags(str_replace("<br />", "\n", $this->versionhtml));

		if(!empty($this->attachments)) 
		{
			$this->headers['Content-type'] = "multipart/mixed; boundary=\"Part-{$this->boundary['mixed']}\"";
			$this->message .= "--Part-{$this->boundary['mixed']}\r\n";
		}
		else if(!empty($this->files))
			$this->headers['Content-type'] = "multipart/related; boundary=\"Part-{$this->boundary['related']}\"";
		else if(!empty($this->versionhtml))
			$this->headers['Content-type'] = "multipart/alternative; boundary=\"Part-{$this->boundary['alternative']}\"";
		else
			$this->headers['Content-type'] = "text/plain; charset=\"us-ascii\"";

		if(!empty($this->files) && !empty($this->attachments))
			$this->message .= $this->wrapHeader("Content-type: multipart/related; boundary=\"Part-{$this->boundary['related']}\"\r\n\r\n");

		if(!empty($this->files))
			$this->message .= "--Part-{$this->boundary['related']}\r\n";

		if(!empty($this->versionhtml) && (!empty($this->files) || !empty($this->attachments)))
			$this->message .= $this->wrapHeader("Content-type: multipart/alternative; boundary=\"Part-{$this->boundary['alternative']}\"\r\n\r\n");

		if(!empty($this->versionhtml))
			$this->message .= "--Part-{$this->boundary['alternative']}\r\n";

		if(!empty($this->versionhtml) || !empty($this->files) || !empty($this->attachments)) 
		{
			$this->message .= "Content-type: text/plain; charset=\"us-ascii\"\r\n";
			$this->message .= "Content-transfer-encoding: 7bit\r\n\r\n";
			$this->message .= $this->versionplain."\r\n\r\n";
		}
		else
			$this->message = $this->versionplain;

		if(!empty($this->versionhtml)) 
		{
			$this->message .= "--Part-{$this->boundary['alternative']}\r\n";
			$this->message .= "Content-type: text/html; charset=\"{$this->charset}\"\r\n";
			$this->message .= "Content-transfer-encoding: quoted-printable\r\n\r\n";
			$this->message .= $this->versionhtml."\r\n\r\n";
			$this->message .= "--Part-{$this->boundary['alternative']}--\r\n";
		}

		if(!empty($this->files)) 
		{
			$this->compileEmbedded();
			$this->message .= "--Part-{$this->boundary['related']}--\r\n";
		}

		if(!empty($this->attachments)) 
		{
			$this->compileAttachments();
			$this->message .= "--Part-{$this->boundary['mixed']}--\r\n";
		}

		$headers = array();
		foreach($this->headers as $k => $v) 
		{
			if(is_array($v) && !empty($v))
				$headers[$k] = $v;
			else if($v != "" && !empty($v))
			{
				if (substr($v,0,strlen("$k:")) != "$k:" )
          			$headers[$k] = wordwrap("$k: $v",75,"\r\n        ");          		
          		else          		
				  	$headers[$k] = $v;          		
			}
		}
		$this->headers = $headers;
		$this->compiled = true;		
	}

	/**
	 * Compile embedded content
	 *
	 * @access public
	 * @since 1.0
	 */
	public function compileEmbedded() 
	{
		foreach($this->files as $current) 
		{
			$this->message .= "--Part-{$this->boundary['related']}\r\n";
			$this->message .= $this->wrapHeader("Content-type: {$current['type']}; name=\"{$current['name']}\"\r\n");
			$this->message .= "Content-ID: <{$current['cid']}>\r\n";
			$this->message .= "Content-transfer-encoding: base64\r\n\r\n";
			$this->message .= "{$current['contents']}\r\n\r\n";
		}
	}

	/**
	 * Compile attachments
	 *
	 * @access public
	 * @since 1.0
	 */
	public function compileAttachments() 
	{
		foreach($this->attachments as $current) 
		{
			$this->message .= "--Part-{$this->boundary['mixed']}\r\n";
			$this->message .= $this->wrapHeader("Content-type: {$current['type']}; name=\"{$current['name']}\"\r\n");
			$this->message .= "Content-disposition: attachment; filename=\"{$current['name']}\"\r\n";
			$this->message .= "Content-transfer-encoding: base64\r\n\r\n";
			$this->message .= "{$current['contents']}\r\n\r\n";
		}
	}

	/**
	 * Set the html content of the mail
	 *
	 * @access public
	 * @param string $data html content
	 * @since 1.0
	 */
	public function setHtml($data) 
	{
		if ($this->charset == 'UTF-8')
			$data = mb_convert_encoding($data,'UTF-8','UTF-8');
		
		if (empty($this->versionplain))
			$this->versionplain = preg_replace_callback("/<a\s[^>]*href=(\"|')([^\"]*)(\"|')[^>]*>(.*)<\/a>/siU", array($this,"compilePlain"), $data);
		
		$this->versionhtml = $this->toQuotedPrintable(str_replace(array("\n","\r","\t"),array("","",""),$data));
	}

	/**
	 * Set the alternative text plain of the mail
	 *
	 * @access public
	 * @param string $data text plain content
	 * @since 1.0
	 */
	public function setPlain($data) 
	{
		$this->versionplain = $this->to7Bit($data);
	}

	/**
	 * Set one header
	 *
	 * @access public
	 * @param string $name key of the header
	 * @param string $data value of the header
	 * @since 1.0
	 */
	public function setHeader($name,$data) 
	{
		if(is_array($this->headers[$name]))
			$this->headers[$name][] = $data;
		else
			$this->headers[$name] = $data;
	}

	/**
	 * Set the charset of the html part
	 *
	 * @access public
	 * @param string $data the charset ('UTF-8','ISO-8859-1', ...)
	 * @since 1.0
	 */
	public function setCharset($data) 
	{
		$this->charset = $data;
	}

	/**
	 * Set the sender of the mail
	 *
	 * @access public
	 * @param string $email sender email
	 * @param string $name sender name
	 * @since 1.0
	 */
	public function setSender($email,$name=null) 
	{		
		$this->headers['From'] = "$name <$email>";
		if(empty($this->headers['Return-Path']))
			$this->headers['Return-Path'] = "$name <$email>";		
	}

	/**
	 * Set the return-path of the mail (Undelivery)
	 *
	 * @access public
	 * @param string $email return-path email
	 * @param string $name return-path name
	 * @since 1.0
	 */
	public function setReturn($email,$name=null) 
	{		
		$this->headers['Return-Path'] = "<$email>";
	}
	
	/**
	 * Set the reply of the mail
	 *
	 * @access public
	 * @param string $email reply email
	 * @param string $name reply name
	 * @since 1.0
	 */
	public function setReply($email,$name=null) 
	{		
		$this->headers['Reply-To'] = $name.'<'.$email.'>';    
	}	
	
	/**
	 * Set the subject of the mail
	 *
	 * @access public
	 * @param string $data the mail subject
	 * @since 1.0
	 */
	public function setSubject($data) 
	{
		if ($this->charset == 'UTF-8')
			$data = SLS_String::makeEmailSubject($data);
		
		$this->headers['Subject'] = $data;
	}

	/**
	 * Add a recipient to the queue
	 *
	 * @access public
	 * @param string $email recipient email
	 * @param string $type recipient type (To, Copy, Hidden Copy)
	 * @since 1.0
	 */
	public function addRecipient($email,$type) 
	{
		$type = ucfirst($type);		
		if(($type != "To" && $type != "Cc" && $type != "Bcc") || !$this->checkEmail($email))
			return $this->error("$email is not a valid recipient.");
		$this->headers[$type][] = $email;		
	}
	
	/**
	 * Delete all the recipients from the queue
	 *
	 * @access public
	 * @since 1.0
	 */
	public function deleteRecipients()
	{	  
	    for ($i=0 ; $i<count($this->headers['To']) ; $i++)
	      array_splice($this->headers['To'], $i, 1);
	}

	/**
	 * Set the X-Mailer
	 *
	 * @access public
	 * @param string $data the X-Mailer
	 * @since 1.0
	 */
	public function setXMailer($data) 
	{
		$this->headers['X-Mailer'] = $data;
	}

	/**
	 * Add an attachment to the mail
	 *
	 * @access public
	 * @param string $filename the filename's path
	 * @param string $data file's content (optionnal)
	 * @since 1.0
	 */
	public function addAttachment($filename,$data=null) 
	{
		$file = array();
		if($data == null) 
		{
			if($fp = @fopen($filename,"rb")) 
			{
				$data = fread($fp,filesize($filename));
				@fclose($fp);
			}
			else
				$data = "";
		}
		$file['name'] = substr($filename,strstr($filename,"/")? strrpos($filename,"/")+1 : 0);
		$file['type'] = strstr($filename,".")? substr($filename,strrpos($filename,".")+1) : "";
		$file['contents'] = $this->toBase64($data);

		if(!empty($this->attachtypes[$file['type']]))
			$file['type'] = $this->attachtypes[$file['type']];
		else
			$file['type'] = "application/octet-stream";

		$this->attachments[] = $file;
	}

	/**
	 * Add embedded content to the email
	 *
	 * @access public
	 * @param string $data embedded content
	 * @param string $filename filename to embed
	 * @return string the uniqid of the embedded content
	 * @since 1.0
	 */
	public function addEmbedded($data,$filename) 
	{
		$file = array();
		$file['cid'] = md5(uniqid(microtime()));
		$file['name'] = substr($filename,strstr($filename,"/")? strrpos($filename,"/")+1 : 0);
		$file['type'] = $this->filetypes[substr($filename,strrpos($filename,".")+1)];
		$file['contents'] = $this->toBase64($data);

		$this->files[] = $file;

		return $file['cid'];
	}

	/**
	 * Parse the html content
	 *
	 * @access public
	 * @param string $data the data to parse
	 * @return string $data the data parsed
	 * @since 1.0
	 */
	public function parseHtml($data) 
	{
		global $HTTP_SERVER_VARS;

		preg_match_all("/([\"\']{1}[^(\"|\')]+\.(".(implode("|",array_flip($this->filetypes))).")[\"\']{1})/Ui",$data,$filelist);

		$filelist = array_unique($filelist[0]);

		foreach($filelist as $current)
		{
			$current = substr($current,1,strlen($current)-2);
			if(preg_match("/^((http:\/\/)|\/)/",$current) && !empty($HTTP_SERVER_VARS['DOCUMENT_ROOT'])) 
			{
				$temp = preg_replace("/^((http:\/\/([^\/])+\/)|\/){1}/Ui",$HTTP_SERVER_VARS['DOCUMENT_ROOT']."/",$current);
				if($fp = @fopen($temp,"rb")) 
				{
					$filedata = fread($fp,filesize($temp));
					@fclose($fp);
				}
			}
			if(empty($filedata) && $fp = @fopen($current,"rb")) 
			{
				$filedata = fread($fp,1048576);
				@fclose($fp);
			}
			if(empty($filedata))
				$filesrc = preg_replace("/^(\/){1}[^\/]+\//Ui","http://".$HTTP_SERVER_VARS['HTTP_HOST']."/",$current);
			else
				$filesrc = "cid:" . $this->addEmbedded($filedata,substr($current,strstr($current,"/")? strrpos($current,"/")+1 : 0));
			$data = str_replace($current,$filesrc,$data);
		}

		return $data;
	}

	/**
	 * Wrap the header
	 *
	 * @access public
	 * @param string $data the header to wrap
	 * @return string the wrapped header
	 * @since 1.0
	 */
	public function wrapHeader($data) 
	{
		return wordwrap($data,75,"\r\n        ",1);
	}

	/**
	 * Convert to 7 bit
	 *
	 * @access public
	 * @param string $data content to convert
	 * @return string the content converted
	 * @since 1.0
	 */
	public function to7Bit($data) 
	{
		$data = str_replace("\r\n","\n",$data);
		$data = str_replace("\r","\n",$data);
		$data = str_replace("\n","\r\n",$data);
		return wordwrap($data,75,"\r\n",1);
	}
	
	/**
	 * Transform content into quoted printable content
	 *
	 * @access public
	 * @param string $text the content to transform
	 * @return string $encoded the content transformed
	 * @since 1.0
	 */
	public function toQuotedPrintable($text, $header_charset='', $break_lines=1, $email_header = 0) 
	{
		$line_break = "\n";
		$ln=strlen($text);
		$h=(strlen($header_charset)>0);
		if($h)
		{
			$encode = array(
				'='=>1,
				'?'=>1,
				'_'=>1,
				'('=>1,
				')'=>1,
				'<'=>1,
				'>'=>1,
				'@'=>1,
				','=>1,
				';'=>1,
				'"'=>1,
				'\\'=>1,
				'['=>1,
				']'=>1,
				':'=>1,
				/*
				'/'=>1,
				'.'=>1,
				*/
			);
			$s=($email_header ? $encode : array());
			$b=$space=$break_lines=0;
			for($i=0; $i<$ln; ++$i)
			{
				$c = $text[$i];
				if(IsSet($s[$c]))
				{
					$b=1;
					break;
				}
				switch($o=Ord($c))
				{
					case 9:
					case 32:
						$space=$i+1;
						$b=1;
						break 2;
					case 10:
					case 13:
						break 2;
					default:
						if($o<32
						|| $o>127)
						{
							$b=1;
							$s = $encode;
							break 2;
						}
				}
			}
			if($i==$ln)
				return($text);
			if($space>0)
				return(substr($text,0,$space).($space<$ln ? $this->toQuotedPrintable(substr($text,$space), $header_charset, $break_lines, $email_header) : ""));
		}
		for($w=$e='',$n=0, $l=0,$i=0;$i<$ln; ++$i)
		{
			$c = $text[$i];
			$o=Ord($c);
			$en=0;
			switch($o)
			{
				case 9:
				case 32:
					if(!$h)
					{
						$w=$c;
						$c='';
					}
					else
					{
						if($b)
						{
							if($o==32)
								$c='_';
							else
								$en=1;
						}
					}
					break;
				case 10:
				case 13:
					if(strlen($w))
					{
						if($break_lines
						&& $l+3>75)
						{
							$e.='='.$line_break;
							$l=0;
						}
						$e.=sprintf('=%02X',Ord($w));
						$l+=3;
						$w='';
					}
					$e.=$c;
					if($h)
						$e.="\t";
					$l=0;
					continue 2;
				case 46:
				case 70:
				case 102:
					$en=(!$h && ($l==0 || $l+1>75));
					break;
				default:
					if($o>127
					|| $o<32
					|| !strcmp($c,'='))
						$en=1;
					elseif($h
					&& IsSet($s[$c]))
						$en=1;
					break;
			}
			if(strlen($w))
			{
				if($break_lines
				&& $l+1>75)
				{
					$e.='='.$line_break;
					$l=0;
				}
				$e.=$w;
				++$l;
				$w='';
			}
			if(strlen($c))
			{
				if($en)
				{
					$c=sprintf('=%02X',$o);
					$el=3;
					$n=1;
					$b=1;
				}
				else
					$el=1;
				if($break_lines
				&& $l+$el>75)
				{
					$e.='='.$line_break;
					$l=0;
				}
				$e.=$c;
				$l+=$el;
			}
		}
		if(strlen($w))
		{
			if($break_lines
			&& $l+3>75)
				$e.='='.$line_break;
			$e.=sprintf('=%02X',Ord($w));
		}
		if($h
		&& $n)
			return('=?'.$header_charset.'?q?'.$e.'?=');
		else
			return($e);
	}

	/**
	 * Convert to base 64
	 *
	 * @access public
	 * @param string $data the content to convert
	 * @return string the content converted
	 * @since 1.0
	 */
	public function toBase64($data) 
	{
		return wordwrap(base64_encode($data),76,"\r\n",1);
	}

	/**
	 * Check if it is a valid email address
	 *
	 * @access public
	 * @param string $data email address
	 * @return int 1 if yes, else 0
	 * @since 1.0
	 */
	public function checkEmail($data) 
	{
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[_a-z0-9-]+)*$/i",$data))
			return 1;
		return 0;
	}
	
	/**
	 * Send the email to all the recipients
	 *
	 * @access public
	 * @since 1.0
	 */
	public function send() 
	{
		if (!$this->compiled)
			$this->compileMail();
		
  		$headers = $this->headers;
  		$message = $this->message;
  			
  		// Using remote smtp
  		if (!empty($this->hostname))
  		{
			// If it has 1 recipient min
			if(!empty($headers['To']))
			{
				// Open the smtp connection
				$this->openConnection(); 
				
				// Merge all the recipients ('To','Cc','Bcc') into one array
				$headersTmp = array_merge($headers['To'],empty($this->headers['Cc']) ? array() : $this->headers['Cc'],empty($this->headers['Bcc']) ? array() : $this->headers['Bcc']);
	
				// For all the recipients
				foreach($headersTmp as $key => $value) 
				{
					// Count number of lines of log file  
					if (count(file($this->logurl)) >= 200)    
						$this->deleteLogLine();
					// Add email subject into log
					fwrite($this->fp, date("Y-m-d H:i:s").' - SUBJECT: '.SLS_String::substrAfterFirstDelimiter($this->headers['Subject'],'Subject: ')."\n", 1024);
					
					// Send to the smtp the sender
					fputs($this->server,"MAIL FROM: <".(substr($headers['From'],strrpos($headers['From'],"<")+1,strlen($headers['From'])-strrpos($headers['From'],"<")-2)).">\r\n");    
					// Log smtp response
					$response = $this->readAndLogServerResponse('MAIL From: <'.(substr($headers['From'],strrpos($headers['From'],"<")+1,strlen($headers['From'])-strrpos($headers['From'],"<")-2)).">",$key);
					
					// If smtp answer 250
					if ($this->checkSmtpAnswer($response,"250"))
				    {
						// Send to the smtp the recipient
						fputs($this->server,"RCPT TO: <".$value.">\r\n");
						// Log smtp response
						$response = $this->readAndLogServerResponse('RCPT To: '.$value,$key);
						
						// If smtp answer 250
						if ($this->checkSmtpAnswer($response,"250"))
					    {					          
							// If it have no recipients, exit
							if(empty($headers['To'])) 
							{               		
								// Smtp reset
								fputs($this->server,"RSET\r\n");
								// Log smtp response
								$response = $this->readAndLogServerResponse('RSET:',$key);
								$this->error[$key]=$response;
							}	
							// Else, it's good, write the recipient
							else 
							{
								if(!empty($headers['To']))
									$headers['To'] = "To: ".$value;	 
								// We prepare Smtp putting mail content							
								fputs($this->server,"DATA\r\n");    		
								// Log smtp response
								$response = $this->readAndLogServerResponse('DATA:',$key);
								
								// If smtp answer 354
								if ($this->checkSmtpAnswer($response,"354"))
							    {             
									// Parse mail content and implode headers
									$message = str_replace("\r\n.\r\n","\r\n..\r\n",$message);
									$headersI = implode("\r\n",$headers);
									
									// Send to smtp the mail
									fputs($this->server,$headersI."\r\n".$message."\r\n.\r\n");    		
									// Log smtp response
									$response = $this->readAndLogServerResponse('SENDING MAIL:',$key);
									
									$this->checkSmtpAnswer($response,"250");
								}
							}
						}
					}
				}    	  	
			}
			return $this->error;
  		}
  		// Using mail() function
  		else 
  		{
	  		// Merge all the recipients ('To','Cc','Bcc') into one array
			$headersTmp = array_merge($headers['To'],empty($this->headers['Cc']) ? array() : $this->headers['Cc'],empty($this->headers['Bcc']) ? array() : $this->headers['Bcc']);
	
			// For all the recipients
			foreach($headersTmp as $key => $value) 
			{
				if(!empty($headers['To']))
				{
					$headers['To'] = "To: ".$value;	
					
					// Parse mail content and implode headers
					$message = str_replace("\r\n.\r\n","\r\n..\r\n",$message);
					$headersI = implode("\r\n",$headers);				
					mail("",SLS_String::substrAfterFirstDelimiter($this->headers['Subject'],'Subject: '),$message,$headersI);				
				}
			}
  		}
	}
	
	/**
	 * Check the Smtp answer and reset if no return the good code
	 *
	 * @access public
	 * @param string $response the smtp answer
	 * @param string $code the good code
	 * @return bool true if it's good, else false
	 * @since 1.0
	 */
	public function checkSmtpAnswer($response,$code="250")
	{
		// If response doesn't give the good answer
		if(substr($response,0,3) != $code)
		{
			// Smtp reset			
	  		fputs($this->server,"RSET\r\n");
			// Log smtp response
			$this->readAndLogServerResponse('RSET:',$key); 
			return false;
	    }
	    else
	    	return true;
	}
  
	/**
	* Read Smtp answers and log them
	* 
	* @access public
	* @param string $action the action
	* @param int $id recipient id
	* @return string $response smtp answer   
	* @since 1.0
	*/     
	public function readAndLogServerResponse($action,$id)
	{
		// Get smtp response
		$response = fgets($this->server,1024);    
		// Count number of lines of log file  
		if (count(file($this->logurl)) >= 180)    
		  $this->deleteLogLine();
		// Add smtp response into log
		fwrite($this->fp, date("Y-m-d H:i:s").' - '.$action.' -> '.$response, 1024);
		
		// If it have a smtp code error    
		if((substr($response,0,3) != "220") && 
		   (substr($response,0,3) != "221") && 
		   (substr($response,0,3) != "235") &&
		   (substr($response,0,3) != "250") && 
		   (substr($response,0,3) != "251") &&
		   (substr($response,0,3) != "334") &&
		   (substr($response,0,3) != "354") &&
		   ($id != "-1") &&
		   ($id != "-2")  
		   )
		{
			$this->error[$id]->idx = $id;
			$this->error[$id]->dat = date("Y-m-d H:i:s");
			$this->error[$id]->act = $action;
			$this->error[$id]->err = $response;
		}
		
		// If id = -1, no mails have been sent
		if((substr($response,0,3) != "220") && 
		   (substr($response,0,3) != "221") && 
		   (substr($response,0,3) != "235") &&
		   (substr($response,0,3) != "250") && 
		   (substr($response,0,3) != "251") &&
		   (substr($response,0,3) != "334") &&
		   (substr($response,0,3) != "354") &&
		   ($id == "-1") &&
		   ($id != "-2")  
		   )
		{
			foreach($this->headers['To'] as $key => $value) 
			{
				$this->error[$key]->idx = $key;
				$this->error[$key]->dat = date("d/m/Y H:i:s");
				$this->error[$key]->act = $action;
				$this->error[$key]->err = $response;
			}    
		}		
		return $response;  
	}
  
	/**
	 * Delete the first line of log file
	 *
	 * @access public
	 * @since 1.0
	 */
	public function deleteLogLine()      
	{ 
		$file = "";		
		rewind($this->fp);		
		while(!feof($this->fp))    
		  $lines[]= fgets($this->fp);		
		rewind($this->fp);		
		for($i=1; $i<count($lines); $i++)    
		  $file.=$lines[$i];  		
		chmod($this->logurl,0664);
		$this->closeLogFile();
		unlink($this->logurl);		 
		$this->openLogFile();		
		fputs($this->fp, $file);
	}
  
	/**
	 * Open log file
	 *
	 * @access public
	 * @since 1.0
	 */
	public function openLogFile()
	{
		if (empty($this->fp))
	  		$this->fp = fopen($this->logurl, "a+");	  	
	}
  
	/**
	 * Close log file
	 *
	 * @access public
	 * @since 1.0
	 */
	public function closeLogFile()
	{
		fclose($this->fp);
		$this->fp = NULL;
	}     

    /**
     * Open the smtp connection, call the server and authenticate (optional)     
     * 
     * @access public    
     * @since 1.0
     */
	public function openConnection() 
	{
		// If it doesn't already have a smtp connexion open
	  	if (empty($this->server) && !empty($this->hostname))
		{
			try {
				$this->server = fsockopen($this->hostaddr,$this->smtpport);
			}
			catch (Exception $e)
			{
				SLS_Tracing::addTrace($e);
			}
			
	  		// Open log file
	   		$this->openLogFile();
		    	
		  	fputs($this->server,"HELO {$this->hostname}\r\n");      
		  	
		  	// Log smtp response
	  		$response = $this->readAndLogServerResponse('CONNECT:'.' IP: '.$this->hostaddr.', PORT: '.$this->smtpport,-1);
			        		
			if(substr($response,0,3) != "220")
	  			return $this->error("Could not connect to SMTP server.");
			  
	  		// Log smtp response
	  		$this->readAndLogServerResponse('HELO:',-1);
		  
	  		if ($this->login != "" && $this->password != "")
	  		{      
			    fputs($this->server,"AUTH LOGIN\r\n");
			    // Log smtp response
			    $this->readAndLogServerResponse('AUTH LOGIN:',-1);
			    fputs($this->server,base64_encode($this->login)."\r\n");			    
			    $this->readAndLogServerResponse('LOGIN:',-1);
			    fputs($this->server,base64_encode($this->password)."\r\n");			    
			    $this->readAndLogServerResponse('PASS:',-1);      
	  		}		
		}
	}
	
	/**
	 * Ping a SMTP Connexion
	 *
	 * @access public
	 * @param string $host the host
	 * @param string $port the port
	 * @param string $user the username
	 * @param string $pass the userpassword
	 * @return mixed true if connection succeed, else error message
	 * @since 1.0
	 */
	public function pingConnection($host,$port,$user,$pass)
	{
		$hostAdd = gethostbyname($host);
		try {
			$this->server = @fsockopen($hostAdd,$port);
		}
		catch (Exception $e)
		{
    		return "Could not connect to SMTP server.";
		}
  		$this->openLogFile();	    	
	  	fputs($this->server,"HELO {$host}\r\n");
  		$response = $this->readAndLogServerResponse('CONNECT:'.' IP: '.$hostAdd.', PORT: '.$port,-1);		
		if ((substr($response,0,3) != "220"))
			return $response;
  		$this->readAndLogServerResponse('HELO:',-1);
  		if ($user != "" && $pass != "")
  		{
	  		fputs($this->server,"AUTH LOGIN\r\n");	
		    $this->readAndLogServerResponse('AUTH LOGIN:',-1);
		    fputs($this->server,base64_encode($user)."\r\n");			    
		    $this->readAndLogServerResponse('LOGIN:',-1);
		    fputs($this->server,base64_encode($pass)."\r\n");			    
		    $this->readAndLogServerResponse('PASS:',-1);
  		}
		return true;
	}
	
	/**
	 * Close the smtp connection
	 *
	 * @access public	 
	 * @since 1.0
	 */
	public function closeConnection() 
	{
		if (!empty($this->hostname))
		{
			fputs($this->server,"QUIT\r\n");
		
			// Log smtp response
			$response = $this->readAndLogServerResponse('QUIT:',-2);
			if(substr($response,0,3) != "221")
				return $this->error("Could not close connection to SMTP server.");
			@fclose($this->server);
			// Close log file
			$this->closeLogFile();
		}
	}
	
	/**
	 * Trace server answers
	 * 
	 * @access public
	 * @since 1.0
	 */
	public function getResponsesServer()
	{
		return $this->responseserver;
  	}
  
	/**
	 * Return the errors queue
	 *
	 * @access public
	 * @return array errors
	 * @since 1.0
	 */
	public function getServerError()
	{
		return $this->error;
  	}

  	/**
  	 * Add an error to the queue
  	 *
  	 * @access public
  	 * @param string $error the error
  	 * @return int
  	 * @since 1.0
  	 */
	public function error($error) 
	{
		$this->errors[] = $error;
		return 0;
	}
}
?>