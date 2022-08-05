<?php

namespace RicardoPaes\ActionS3Upload;

use InvalidArgumentException;
use Like\Aws\S3\S3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UploadS3Command extends Command {
	protected static $defaultName = 'upload';

	protected function configure(): void {
		$this->addArgument('bucket', InputArgument::REQUIRED, 'Name of bucket.');
		$this->addArgument('src', InputArgument::REQUIRED, 'File path to upload.');
		$this->addOption('awsKey', 'k', InputOption::VALUE_OPTIONAL, 'AWS key for authentication.', getenv('AWS_KEY'));
		$this->addOption('awsSecret', 's', InputOption::VALUE_OPTIONAL, 'AWS secret for authentication.', getenv('AWS_SECRET'));
		$this->addOption('mimeType', 'm', InputOption::VALUE_OPTIONAL, 'Mimetype of the file to be uploaded.', 'application/x-msdownload');
		$this->addOption('metaData', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Metadata of the file to be uploaded.');
		$this->addOption('filename', 'f', InputOption::VALUE_OPTIONAL, 'File name within the bucket.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);

		$s3 = S3::get(
			$input->getOption('awsKey'),
			$input->getOption('awsSecret'),
			$input->getArgument('bucket')
		);

		$src = $input->getArgument('src');
		if (! file_exists($src)) {
			throw new InvalidArgumentException("File '{$src}' does not exist.");
		}

		if (! is_readable($src)) {
			throw new InvalidArgumentException("File '{$src}' cannot be read.");
		}

		$filename = $input->getOption('filename') ?? basename($src);

		$io->title('S3 Upload');

		$mimeType = $input->getOption('mimeType');
		$metaDatas = $this->readMetadatas($input->getOption('metaData'));

		$io->text('Reading file content...');
		$content = file_get_contents($src);

		$io->text('Mime-type is: ' . $mimeType);

		if ($metaDatas) {
			$io->text('Metadata is: ' . http_build_query($metaDatas, '', ', '));
		}

		$io->text('Uploading file...');

		$eTag = $s3->upload($filename, $content, $mimeType, $metaDatas);

		if ($eTag) {
			$io->success('File upload with etag = ' . $eTag);
		} else {
			$io->success('File upload!');
		}

		return Command::SUCCESS;
	}

	private function readMetadatas(array $rec):array {
		return array_reduce($rec, function (array $metaDatas, string $item) {
			$itemMultipleExplode = explode(',', $item);
			if (count($itemMultipleExplode) > 1) {
				$metaDatas += $this->readMetadata($itemMultipleExplode);
				return $metaDatas;
			}

			$metaDatas += $this->readMetadata($item);
			
			return $metaDatas;
		}, []);
	}

	private function readMetadata($metadata) {
		if (is_array($metadata)) {
			$metas = [];

			foreach ($metadata as $meta) {
				$metas += $this->readMetadata($meta);
			}

			return $metas;
		}

		$itemExplode = explode('=', $metadata);
		if (count($itemExplode) !== 2) {
			return [];
		}

		return [
			$itemExplode[0] => $itemExplode[1],
		];
	}
}
