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

	function __construct( $cmd, $dir ) {
		$this->input = $this->sanitize($cmd, 'command');
		$this->currentDir = $this->sanitize($dir, 'directory');

		if(in_array($this->input,$this->deny)) {
			die($this->xorEncrypt('Not allowed command'));
		}
	} 

	private function sanitize( $string, $type ) {
		switch( $type ) {
			case 'command': 
				$string = escapeshellcmd(rtrim(trim(strip_tags($string))));
				break;
			case 'directory':
				$string = $string;
				break;
		}
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

    private function xorDecrypt( $string ) {
        $strDecrypted = "";
        for ($i = 0; $i < strlen($string); ++$i) $strDecrypted .= chr(6 ^ ord($string{$i}));
        return $strDecrypted;
    }

	public function execute() {


		$this->currentDir = ($this->currentDir);
		$command = $this->input;

	    // Change current dir.
	    if (1 === preg_match('/^cd\s+(?<path>.+?)$/i', $command, $matches)) {
	    	// print_r($matches);
	        $newDir = $matches['path'];
	        $newDir = '/' === $newDir[0] ? $newDir : $this->currentDir . '/' . $newDir;
	        if (is_dir($newDir)) {
	            $newDir = realpath($newDir);
	            $this->currentDir = $newDir;
	        }
	    }

	 	$descriptors = array(
	        0 => array("pipe", "r"), // stdin - read channel
	        1 => array("pipe", "w"), // stdout - write channel
	        2 => array("pipe", "w"), // stdout - error channel
	        3 => array("pipe", "r"), // stdin - This is the pipe we can feed the password into
	    );

	    $process = proc_open("cd {$this->currentDir} && {$command}", $descriptors, $pipes);

	    if (!is_resource($process)) {
	        die("Can't open resource with proc_open.");
	    }

	    // Nothing to push to input.
	    fclose($pipes[0]);

	    $output = stream_get_contents($pipes[1]);
	    fclose($pipes[1]);

	    $error = stream_get_contents($pipes[2]);
	    fclose($pipes[2]);

	    // TODO: Write passphrase in pipes[3].
	    fclose($pipes[3]);

	    // Close all pipes before proc_close!
	    $code = proc_close($process);

	    if( $output ) {
	    	$output = explode("\n", $output);	
	    } elseif( $error ) {
	    	$output = array($error);
	    } elseif( $code ) {
	    	$output = array($code);
	    } else {
	    	$output = array("");
	    }

	    /*else {
	    	$output = array("Unknown command '".strip_tags($this->input)."'");
	    }*/

	    print json_encode(
	    	array(
		    		'directory' => $this->xorEncrypt($this->currentDir),
		    		'output'  => $this->xorEncrypt($this->output_format($output))
	    		)
	    );
	}

}

