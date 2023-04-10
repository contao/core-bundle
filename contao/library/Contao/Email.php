<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\CoreBundle\Monolog\ContaoContext;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as EmailMessage;

/**
 * A Mailer adapter class
 *
 * The class functions as an adapter for the Symfony mailer framework. It can be
 * used to send e-mails via the PHP mail function or an SMTP server.
 *
 * Usage:
 *
 *     $email = new Email();
 *     $email->subject = 'Hello';
 *     $email->text = 'Is it me you are looking for?';
 *     $email->sendTo('lionel@richie.com');
 *
 * @property string  $subject     The e-mail subject
 * @property string  $text        The text part of the mail
 * @property string  $html        The HTML part of the mail
 * @property string  $from        The sender's e-mail address
 * @property string  $fromName    The sender's name
 * @property string  $priority    The e-mail priority
 * @property string  $charset     The e-mail character set
 * @property string  $imageDir    The base directory to look for internal images
 * @property boolean $embedImages Whether to embed images inline
 * @property string  $logFile     The log file path
 * @property array   $failures    An array of rejected e-mail addresses
 */
class Email
{
	/**
	 * Mailer object
	 * @var \Swift_Mailer|MailerInterface
	 */
	protected $objMailer;

	/**
	 * Message object
	 * @var \Swift_Message|EmailMessage
	 */
	protected $objMessage;

	/**
	 * Sender e-mail address
	 * @var string
	 */
	protected $strSender;

	/**
	 * Sender name
	 * @var string
	 */
	protected $strSenderName;

	/**
	 * E-mail priority
	 * @var integer
	 */
	protected $intPriority;

	/**
	 * E-mail subject
	 * @var string
	 */
	protected $strSubject;

	/**
	 * Text part of the e-mail
	 * @var string
	 */
	protected $strText;

	/**
	 * HTML part of the e-mail
	 * @var string
	 */
	protected $strHtml;

	/**
	 * Character set
	 * @var string
	 */
	protected $strCharset;

	/**
	 * Image directory
	 * @var string
	 */
	protected $strImageDir;

	/**
	 * Embed images
	 * @var boolean
	 */
	protected $blnEmbedImages = true;

	/**
	 * Invalid addresses
	 * @var array
	 */
	protected $arrFailures = array();

	/**
	 * Log file name
	 * @var string
	 */
	protected $strLogFile = 'EMAIL';

	/**
	 * Instantiate the object and load the mailer framework
	 *
	 * @param \Swift_Mailer|MailerInterface|null $objMailer
	 */
	public function __construct($objMailer = null)
	{
		$this->objMailer = $objMailer ?: System::getContainer()->get('mailer');
		$this->strCharset = System::getContainer()->getParameter('kernel.charset');

		if ($this->objMailer instanceof MailerInterface)
		{
			$this->objMessage = new EmailMessage();
		}
		elseif ($this->objMailer instanceof \Swift_Mailer)
		{
			$this->objMessage = new \Swift_Message();
		}
		else
		{
			throw new \InvalidArgumentException('Invalid mailer instance given. Only Swift_Mailer and instances of ' . MailerInterface::class . ' are supported.');
		}
	}

	/**
	 * Set an object property
	 *
	 * @param string $strKey   The property name
	 * @param mixed  $varValue The property value
	 *
	 * @throws \Exception If $strKey is unknown
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'subject':
				$this->strSubject = preg_replace(array('/[\t]+/', '/[\n\r]+/'), array(' ', ''), $varValue);
				break;

			case 'text':
				$this->strText = StringUtil::decodeEntities($varValue);
				break;

			case 'html':
				$this->strHtml = $varValue;
				break;

			case 'from':
				$this->strSender = $varValue;
				break;

			case 'fromName':
				$this->strSenderName = $varValue;
				break;

			case 'priority':
				switch ($varValue)
				{
					case 1:
					case 'highest':
						$this->intPriority = 1;
						break;

					case 2:
					case 'high':
						$this->intPriority = 2;
						break;

					case 3:
					case 'normal':
						$this->intPriority = 3;
						break;

					case 4:
					case 'low':
						$this->intPriority = 4;
						break;

					case 5:
					case 'lowest':
						$this->intPriority = 5;
						break;
				}
				break;

			case 'charset':
				$this->strCharset = $varValue;
				break;

			case 'imageDir':
				$this->strImageDir = $varValue;
				break;

			case 'embedImages':
				$this->blnEmbedImages = $varValue;
				break;

			case 'logFile':
				$this->strLogFile = $varValue;
				break;

			default:
				throw new \Exception(sprintf('Invalid argument "%s"', $strKey));
		}
	}

	/**
	 * Return an object property
	 *
	 * @param string $strKey The property name
	 *
	 * @return mixed The property value
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'subject':
				return $this->strSubject;

			case 'text':
				return $this->strText;

			case 'html':
				return $this->strHtml;

			case 'from':
				return $this->strSender;

			case 'fromName':
				return $this->strSenderName;

			case 'priority':
				return $this->intPriority;

			case 'charset':
				return $this->strCharset;

			case 'imageDir':
				return $this->strImageDir;

			case 'embedImages':
				return $this->blnEmbedImages;

			case 'logFile':
				return $this->strLogFile;

			case 'failures':
				return $this->arrFailures;
		}

		return null;
	}

	/**
	 * Return true if there are failures
	 *
	 * @return boolean True if there are failures
	 */
	public function hasFailures()
	{
		return !empty($this->arrFailures);
	}

	/**
	 * Add a custom text header
	 *
	 * @param string $strKey   The header name
	 * @param string $strValue The header value
	 */
	public function addHeader($strKey, $strValue)
	{
		$this->objMessage->getHeaders()->addTextHeader($strKey, $strValue);
	}

	/**
	 * Add CC e-mail addresses
	 *
	 * Friendly name portions (e.g. Admin <admin@example.com>) are allowed. The
	 * method takes an unlimited number of recipient addresses.
	 */
	public function sendCc()
	{
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->cc(...$this->compileRecipients(\func_get_args()));
		}
		else
		{
			$this->objMessage->setCc($this->compileRecipients(\func_get_args()));
		}
	}

	/**
	 * Add BCC e-mail addresses
	 *
	 * Friendly name portions (e.g. Admin <admin@example.com>) are allowed. The
	 * method takes an unlimited number of recipient addresses.
	 */
	public function sendBcc()
	{
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->bcc(...$this->compileRecipients(\func_get_args()));
		}
		else
		{
			$this->objMessage->setBcc($this->compileRecipients(\func_get_args()));
		}
	}

	/**
	 * Add ReplyTo e-mail addresses
	 *
	 * Friendly name portions (e.g. Admin <admin@example.com>) are allowed. The
	 * method takes an unlimited number of recipient addresses.
	 */
	public function replyTo()
	{
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->replyTo(...$this->compileRecipients(\func_get_args()));
		}
		else
		{
			$this->objMessage->setReplyTo($this->compileRecipients(\func_get_args()));
		}
	}

	/**
	 * Attach a file
	 *
	 * @param string $strFile The file path
	 * @param string $strMime The MIME type (defaults to "application/octet-stream")
	 */
	public function attachFile($strFile, $strMime='application/octet-stream')
	{
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->attachFromPath($strFile, basename($strFile), $strMime);
		}
		else
		{
			$this->objMessage->attach(\Swift_Attachment::fromPath($strFile, $strMime)->setFilename(basename($strFile)));
		}
	}

	/**
	 * Attach a file from a string
	 *
	 * @param string $strContent  The file content
	 * @param string $strFilename The file name
	 * @param string $strMime     The MIME type (defaults to "application/octet-stream")
	 */
	public function attachFileFromString($strContent, $strFilename, $strMime='application/octet-stream')
	{
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->attach($strContent, $strFilename, $strMime);
		}
		else
		{
			$this->objMessage->attach(new \Swift_Attachment($strContent, $strFilename, $strMime));
		}
	}

	/**
	 * Send the e-mail
	 *
	 * Friendly name portions (e.g. Admin <admin@example.com>) are allowed. The
	 * method takes an unlimited number of recipient addresses.
	 *
	 * @return boolean True if the e-mail was sent successfully
	 */
	public function sendTo()
	{
		$arrRecipients = $this->compileRecipients(\func_get_args());

		if (empty($arrRecipients))
		{
			return false;
		}

		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->to(...$arrRecipients);
		}
		else
		{
			$this->objMessage->setTo($arrRecipients);
			$this->objMessage->setCharset($this->strCharset);
		}

		// Add the priority if it has been set (see #608)
		if ($this->intPriority !== null)
		{
			if ($this->objMessage instanceof EmailMessage)
			{
				$this->objMessage->priority($this->intPriority);
			}
			else
			{
				$this->objMessage->setPriority($this->intPriority);
			}
		}

		// Default subject
		if (!$this->strSubject)
		{
			$this->strSubject = 'No subject';
		}

		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->subject($this->strSubject);
		}
		else
		{
			$this->objMessage->setSubject($this->strSubject);
		}

		// HTML e-mail
		if ($this->strHtml)
		{
			// Embed images
			if ($this->blnEmbedImages)
			{
				if (!$this->strImageDir)
				{
					$this->strImageDir = System::getContainer()->getParameter('kernel.project_dir') . '/';
				}

				$arrCid = array();
				$arrMatches = array();
				$strBase = Environment::get('base');

				// Thanks to @ofriedrich and @aschempp (see #4562)
				preg_match_all('/<[a-z][a-z0-9]*\b[^>]*((src=|background=|url\()["\']??)(.+\.(jpe?g|png|gif|bmp|tiff?|swf))(["\' ]??(\)??))[^>]*>/Ui', $this->strHtml, $arrMatches);

				// Check for internal images
				if (!empty($arrMatches) && isset($arrMatches[0]))
				{
					for ($i=0, $c=\count($arrMatches[0]); $i<$c; $i++)
					{
						$url = $arrMatches[3][$i];

						// Try to remove the base URL
						$src = str_replace($strBase, '', $url);
						$src = rawurldecode($src); // see #3713

						// Embed the image if the URL is now relative
						if (!preg_match('@^https?://@', $src) && ($objFile = new File(StringUtil::stripRootDir($this->strImageDir . $src))) && ($objFile->exists() || $objFile->createIfDeferred()))
						{
							if (!isset($arrCid[$src]))
							{
								if ($this->objMessage instanceof EmailMessage)
								{
									// See https://symfony.com/doc/current/mailer.html#embedding-images
									$this->objMessage->embedFromPath($this->strImageDir . $src, $src);
									$arrCid[$src] = 'cid:' . $src;
								}
								else
								{
									$arrCid[$src] = $this->objMessage->embed(\Swift_EmbeddedFile::fromPath($this->strImageDir . $src));
								}
							}

							$this->strHtml = str_replace($arrMatches[1][$i] . $arrMatches[3][$i] . $arrMatches[5][$i], $arrMatches[1][$i] . $arrCid[$src] . $arrMatches[5][$i], $this->strHtml);
						}
					}
				}
			}

			if ($this->objMessage instanceof EmailMessage)
			{
				$this->objMessage->html($this->strHtml, $this->strCharset);
			}
			else
			{
				$this->objMessage->setBody($this->strHtml, 'text/html');
			}
		}

		// Text content
		if ($this->strText)
		{
			if ($this->objMessage instanceof EmailMessage)
			{
				$this->objMessage->text($this->strText, $this->strCharset);
			}
			elseif ($this->strHtml)
			{
				$this->objMessage->addPart($this->strText, 'text/plain');
			}
			else
			{
				$this->objMessage->setBody($this->strText, 'text/plain');
			}
		}

		// Add the administrator e-mail as default sender
		if (!$this->strSender)
		{
			if (!empty($GLOBALS['TL_ADMIN_EMAIL']))
			{
				$this->strSender = $GLOBALS['TL_ADMIN_EMAIL'];
				$this->strSenderName = $GLOBALS['TL_ADMIN_NAME'] ?? null;
			}
			elseif ($adminEmail = Config::get('adminEmail'))
			{
				list($this->strSenderName, $this->strSender) = StringUtil::splitFriendlyEmail($adminEmail);
			}
			else
			{
				throw new \Exception('No administrator e-mail address has been set.');
			}
		}

		// Sender
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->from(new Address($this->strSender, $this->strSenderName ?? ''));
		}
		elseif ($this->strSenderName)
		{
			$this->objMessage->setFrom(array($this->strSender=>$this->strSenderName));
		}
		else
		{
			$this->objMessage->setFrom($this->strSender);
		}

		// Set the return path (see #5004)
		if ($this->objMessage instanceof EmailMessage)
		{
			$this->objMessage->returnPath($this->strSender);
		}
		else
		{
			$this->objMessage->setReturnPath($this->strSender);
		}

		// Send the e-mail
		$this->objMailer->send($this->objMessage);

		$arrCc = $this->objMessage->getCc();
		$arrBcc = $this->objMessage->getBcc();

		// Add a log entry
		$strMessage = 'An e-mail has been sent to ';

		if ($this->objMessage instanceof EmailMessage)
		{
			$addresscb = static function (Address $address) {
				return $address->getAddress();
			};

			$strMessage .= implode(', ', array_map($addresscb, $this->objMessage->getTo()));

			if (!empty($arrCc))
			{
				$strMessage .= ', CC to ' . implode(', ', array_map($addresscb, $arrCc));
			}

			if (!empty($arrBcc))
			{
				$strMessage .= ', BCC to ' . implode(', ', array_map($addresscb, $arrBcc));
			}
		}
		else
		{
			$strMessage .= implode(', ', array_keys($this->objMessage->getTo()));

			if (!empty($arrCc))
			{
				$strMessage .= ', CC to ' . implode(', ', array_keys($arrCc));
			}

			if (!empty($arrBcc))
			{
				$strMessage .= ', BCC to ' . implode(', ', array_keys($arrBcc));
			}
		}

		$context = array();

		if ($this->strLogFile !== ContaoContext::EMAIL)
		{
			$context = array('contao' => new ContaoContext(__METHOD__, $this->strLogFile));
		}

		System::getContainer()->get('monolog.logger.contao.email')->info($strMessage, $context);

		return true;
	}

	/**
	 * Extract the e-mail addresses from the func_get_args() arguments
	 *
	 * @param array $arrRecipients The recipients array
	 *
	 * @return array An array of e-mail addresses
	 */
	protected function compileRecipients($arrRecipients)
	{
		$arrReturn = array();

		foreach ($arrRecipients as $varRecipients)
		{
			if (!\is_array($varRecipients))
			{
				$varRecipients = StringUtil::splitCsv($varRecipients);
			}

			// Support friendly name addresses and internationalized domain names
			foreach ($varRecipients as $v)
			{
				list($strName, $strEmail) = StringUtil::splitFriendlyEmail($v);

				$strName = trim($strName, ' "');
				$strEmail = Idna::encodeEmail($strEmail);

				if ($this->objMessage instanceof EmailMessage)
				{
					$arrReturn[] = new Address($strEmail, $strName);
				}
				elseif ($strName)
				{
					$arrReturn[$strEmail] = $strName;
				}
				else
				{
					$arrReturn[] = $strEmail;
				}
			}
		}

		return $arrReturn;
	}
}
