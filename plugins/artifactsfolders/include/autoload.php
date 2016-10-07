<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloaddaeb4644ba1509468a855477328f2346($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'artifactsfoldersplugin' => '/artifactsfoldersPlugin.class.php',
            'tuleap\\artifactsfolders\\artifactsfoldersplugindescriptor' => '/ArtifactsFoldersPluginDescriptor.php',
            'tuleap\\artifactsfolders\\artifactsfoldersplugininfo' => '/ArtifactsFoldersPluginInfo.php',
            'tuleap\\artifactsfolders\\folder\\artifactlinkinformationprepender' => '/Folder/ArtifactLinkInformationPrepender.php',
            'tuleap\\artifactsfolders\\folder\\artifactpresenter' => '/Folder/ArtifactPresenter.php',
            'tuleap\\artifactsfolders\\folder\\artifactpresenterbuilder' => '/Folder/ArtifactPresenterBuilder.php',
            'tuleap\\artifactsfolders\\folder\\artifactview' => '/Folder/ArtifactView.php',
            'tuleap\\artifactsfolders\\folder\\controller' => '/Folder/Controller.php',
            'tuleap\\artifactsfolders\\folder\\dao' => '/Folder/Dao.php',
            'tuleap\\artifactsfolders\\folder\\datafromrequestaugmentor' => '/Folder/DataFromRequestAugmentor.php',
            'tuleap\\artifactsfolders\\folder\\folderforartifactgoldenretriever' => '/Folder/FolderForArtifactGoldenRetriever.php',
            'tuleap\\artifactsfolders\\folder\\folderusageretriever' => '/Folder/FolderUsageRetriever.php',
            'tuleap\\artifactsfolders\\folder\\postsavenewchangesetcommand' => '/Folder/PostSaveNewChangesetCommand.php',
            'tuleap\\artifactsfolders\\folder\\presenter' => '/Folder/Presenter.php',
            'tuleap\\artifactsfolders\\folder\\router' => '/Folder/Router.php',
            'tuleap\\artifactsfolders\\nature\\natureinfolderpresenter' => '/Nature/NatureInFolderPresenter.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloaddaeb4644ba1509468a855477328f2346');
// @codeCoverageIgnoreEnd
