<?php

namespace RicardoPaes\ActionS3Upload;

use InvalidArgumentException;
use Like\Aws\Aws;
use Like\Aws\S3\S3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadS3Command extends Command {
	protected static $defaultName = 'download';

	protected function configure(): void {
		$this->addArgument('bucket', InputArgument::REQUIRED, 'Name of bucket.');
		$this->addArgument('src', InputArgument::REQUIRED, 'File path to upload.');
		$this->addArgument('dest', InputArgument::REQUIRED, 'Location to save file.');
		$this->addOption('awsKey', 'k', InputOption::VALUE_OPTIONAL, 'AWS key for authentication.', getenv('AWS_KEY'));
		$this->addOption('awsSecret', 's', InputOption::VALUE_OPTIONAL, 'AWS secret for authentication.', getenv('AWS_SECRET'));
		$this->addOption('region', 'r', InputOption::VALUE_OPTIONAL, 'Region where the bucket is located.', Aws::REGION_NORTH_VIRGINIA);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);

		$region = trim($input->getOption('region')) ?? Aws::REGION_NORTH_VIRGINIA;
		$s3 = S3::get(
			$input->getOption('awsKey'),
			$input->getOption('awsSecret'),
			$input->getArgument('bucket'),
			$region
		);

		$src = $input->getArgument('src');
		$dest = $input->getArgument('dest') ?? $src;

		$io->title('S3 Download');

		$io->text('Region: ' . $region);
		$io->text('Src is: "' . $src . '".');

		$io->text('Downloading file...');

		$s3File = $s3->download($src);
		$saved = file_put_contents($dest, $s3File->getBody());

		if($saved !== false) {
			$io->success(sprintf('File download in %s!', $dest));
		}

		return Command::SUCCESS;
	}

}
