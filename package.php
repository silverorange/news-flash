<?php

require_once 'PEAR/PackageFileManager2.php';

$version = '0.1.16';
$notes = <<<EOT
No release notes for you!
EOT;

$description =<<<EOT
Composite aggregation of news items for use with the Site package.
EOT;

$package = new PEAR_PackageFileManager2();
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$result = $package->setOptions(
	array(
		'filelistgenerator' => 'file',
		'simpleoutput'      => true,
		'baseinstalldir'    => '/',
		'packagedirectory'  => './',
		'dir_roles'         => array(
			'NewsFlash'     => 'php',
			'locale'        => 'data',
			'www'           => 'data',
			'/'             => 'data',
		),
	)
);

$package->setPackage('NewsFlash');
$package->setSummary('News aggregator');
$package->setDescription($description);
$package->setChannel('pear.silverorange.com');
$package->setPackageType('php');
$package->setLicense('LGPL', 'http://www.gnu.org/copyleft/lesser.html');

$package->setReleaseVersion($version);
$package->setReleaseStability('alpha');
$package->setAPIVersion('0.1.0');
$package->setAPIStability('alpha');
$package->setNotes($notes);

$package->addIgnore('package.php');

$package->addMaintainer(
	'lead',
	'gauthierm',
	'Mike Gauthier',
	'mike@silverorange.com'
);

$package->addReplacement(
	'NewsFlash/NewsFlash.php',
	'pear-config',
	'@DATA-DIR@',
	'data_dir'
);

$package->setPhpDep('5.1.5');
$package->setPearinstallerDep('1.4.0');

$package->addPackageDepWithChannel(
	'required',
	'Swat',
	'pear.silverorange.com',
	'0.9.2'
);

$package->addPackageDepWithChannel(
	'required',
	'Site',
	'pear.silverorange.com',
	'1.5.25'
);

$package->addPackageDepWithChannel(
	'required',
	'HTTP_Request2',
	'pear.php.net',
	'0.5.2'
);

$package->addPackageDepWithChannel(
	'optional',
	'Services_Twitter',
	'pear.php.net',
	'0.5.1'
);

$package->addPackageDepWithChannel(
	'optional',
	'HTTP_OAuth',
	'pear.php.net',
	'0.2.3'
);

$package->addPackageDepWithChannel(
	'optional',
	'Sniftr',
	'pear.silverorange.com',
	'0.1.6'
);

$package->addPackageDepWithChannel(
	'optional',
	'Deliverance',
	'pear.silverorange.com',
	'0.2.33'
);

$package->addExtensionDep('optional', 'json');
$package->addExtensionDep('required', 'dom');

$package->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
	$package->writePackageFile();
} else {
	$package->debugPackageFile();
}

?>
