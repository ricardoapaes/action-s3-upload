<?php

namespace RicardoPaes\ActionS3Upload;

use Symfony\Component\Console\Application;

class App extends Application {
	public function __construct() {
		parent::__construct('s3-upload');
		$this->add(new UploadS3Command());
	}
}
