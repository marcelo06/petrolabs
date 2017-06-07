<?php
class settings
{
	var $pager;
	var $domain;
	var $sendersEmail;
	var $pw;

	function __construct ()
	{
		$this->pager = 20;
		$this->domain = "aditivospetrolabs.com";
		$this->sendersEmail = "envio@aditivospetrolabs.com";
		$this->pw = "52P#hahmH)y5";
	}
}
?>