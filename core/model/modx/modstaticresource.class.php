<?php
/*
 * This file is part of MODX Revolution.
 *
 * Copyright (c) MODX, LLC. All Rights Reserved.
 *
 * For complete copyright and license information, see the COPYRIGHT and LICENSE
 * files found in the top-level directory of this distribution.
 */

/**
 * A derivative of modResource that stores content on the filesystem.
 *
 * {@inheritdoc}
 *
 * @package modx
 */
class modStaticResource extends modResource implements modResourceInterface {
    /**
     * @var string Path of the file containing the source content, relative to the media source or full absolute path
     */
    protected $_sourceFile= '';
    /**
     * @var array
     */
    protected $_sourceContents = [];
    /**
     * @var integer Size of the source file content in bytes.
     */
    protected $_sourceFileSize= 0;
    /**
     * @var modMediaSource|null
     */
    protected $source;
    /**
     * @var bool
     */
    protected $sourceConfigured;

    /**
     * Overrides modResource::__construct to set the class key for this Resource type
     * @param xPDO $xpdo A reference to the xPDO|modX instance
     */
    function __construct(& $xpdo) {
        parent :: __construct($xpdo);
        $this->set('class_key','modStaticResource');
        $this->showInContextMenu = true;
        $this->getMediaSource();
    }

    /**
     * Get the absolute path to the static source file represented by this instance.
     *
     * @param array $options An array of options.
     * @return string The absolute path to the static source file.
     */
    public function getSourceFile(array $options = array()) {
        $filename = (string)parent::getContent($options);

        // Support placeholders/snippets in the filename by parsing it through the modParser
        $array = array();
        if ($this->xpdo->getParser() && $this->xpdo->parser->collectElementTags($filename, $array)) {
            $this->xpdo->parser->processElementTags('', $filename);
        }

        // Sanitize to avoid ../ style path traversal
        $filename = preg_replace(array("/\.*[\/|\\\]/i", "/[\/|\\\]+/i"), array('/', '/'), $filename);

        // If a media source was configured, always use the file as a relative path within the media source to enforce
        // proper access control, and attempt to load the file through the media source APIs.
        if ($this->sourceConfigured) {
            $this->_sourceFile = $filename;
            $this->_sourceContents = $this->source ? $this->source->getObjectContents($this->_sourceFile) : [];
            $this->_sourceFileSize = array_key_exists('size', $this->_sourceContents) ? $this->_sourceContents['size'] : 0;
            return $this->_sourceFile;
        }

        // If absolute paths are allowed (disabled by default for security reasons), and a file exists at the provided path, use it
        $allowAbsolute = (bool)$this->xpdo->getOption('resource_static_allow_absolute', null, false);
        if ($allowAbsolute && file_exists($filename)) {
            $this->_sourceFile = $filename;
            $this->_sourceFileSize = filesize($filename);
        }

        // If absolute paths are **not** allowed or an absolute file was not found, prefix the resource_static_path setting
        else {
            $sourcePath = $this->xpdo->getOption('resource_static_path', $options, '{core_path}/static/', true);
            if ($this->xpdo->getParser() && $this->xpdo->parser->collectElementTags($sourcePath, $array)) {
                $this->xpdo->parser->processElementTags('', $sourcePath);
            }
            $this->_sourceFile = $sourcePath . $filename;
            if (file_exists($this->_sourceFile)) {
                $this->_sourceFileSize = filesize($filename);
            }
        }

        return $this->_sourceFile;
    }

    /**
     * Get the filesize of the static source file represented by this instance.
     *
     * @param array $options An array of options.
     * @return integer The filesize of the source file in bytes.
     */
    public function getSourceFileSize(array $options = array()) {
        $this->getSourceFile($options);
        return $this->_sourceFileSize;
    }

    /**
     * Treats the local content as a filename to load the raw content from.
     *
     * For resources with a binary content type, this renders out the file to the browser immediately.
     *
     * {@inheritdoc}
     */
    public function getContent(array $options = array()) {
        $this->getSourceFile($options);
        $content = $this->getFileContent($this->_sourceFile);
        if ($content === false) {
            $this->xpdo->sendErrorPage();
        }
        return $content;
    }

    /**
     * Retrieve the resource content stored in a physical file.
     *
     * @param string $file @deprecated internal _sourceFile is always used
     * @param array $options
     * @return string The content of the file, of false if it could not be
     * retrieved.
     */
    public function getFileContent($file, array $options = array()) {
        /** @var modContentType $contentType */
        $contentType = $this->getOne('ContentType');
        if (!$contentType) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "modStaticResource->getFileContent() for resource {$this->get('id')}: Could not get content type.");
            return false;
        }

        $content = false;
        $streamable = false;
        if ($this->sourceConfigured) {
            $content = array_key_exists('content', $this->_sourceContents) ? $this->_sourceContents['content'] : '';
        }
        elseif (file_exists($this->_sourceFile) && is_readable($this->_sourceFile)) {
            $content = $this->_sourceFile;
            $streamable = true;
        }

        if (empty($content)) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "modStaticResource->getFileContent() for resource {$this->get('id')}: Could not load content from file {$this->_sourceFile}");
            return false;
        }

        // Set the appropriate content type and charset for non-binary content types
        $mimeType = $contentType->get('mime_type') ? $contentType->get('mime_type') : 'text/html';
        $header = 'Content-Type: ' . $mimeType;
        if (!$contentType->get('binary')) {
            $charset= $this->xpdo->getOption('modx_charset',null,'UTF-8');
            $header .= '; charset=' . $charset;
        }
        header($header);

        if ($contentType->get('binary')) {
            // @todo This header dates back to pre-git revo circa 2008, but seems to be an email header and may need fixing
            header('Content-Transfer-Encoding: binary');
        }

        // Apply a content-length header if we know the size in bytes
        $filesize = $this->getSourceFileSize($options);
        if ($filesize > 0) {
            header('Content-Length: ' . $filesize);
        }

        // Set content disposition header based on what's configured on the resource (bool)
        if ($this->get('content_dispo')) {
            $name = $this->getAttachmentName($contentType);
            header('Content-Disposition: attachment; filename=' . $name);
        }
        else {
            header('Content-Disposition: inline');
        }

        // Cache control headers
        header('Cache-Control: public');
        header('Vary: User-Agent');

        // Custom headers defined on the content type, if any
        if ($customHeaders = $contentType->get('headers')) {
            foreach ($customHeaders as $headerKey => $headerString) {
                header($headerString);
            }
        }

        // Close the user session, clean out the output buffer
        @session_write_close();
        while (ob_get_level() && @ob_end_clean()) {}

        // Output the content, either streaming with readfile() or by echo'ing content that was retrieved earlier from media source
        if ($streamable) {
            readfile($content);
        }
        else {
            echo $content;
        }

        exit();
    }

    /**
     * Converts to bytes from PHP ini_get() format.
     *
     * PHP ini modifiers for byte values:
     * <ul>
     *  <li>G = gigabytes</li>
     *  <li>M = megabytes</li>
     *  <li>K = kilobytes</li>
     * </ul>
     *
     * @access protected
     * @param string $value Number of bytes represented in PHP ini value format.
     * @return integer The value converted to bytes.
     */
    protected function _bytes($value) {
        $value = trim($value);
        $modifier = strtolower($value[strlen($value)-1]);
        switch($modifier) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        return $value;
    }

    /**
     * Sets the path to the Static Resource manager controller
     * @static
     * @param xPDO $modx A reference to the modX instance
     * @return string
     */
    public static function getControllerPath(xPDO &$modx) {
        $path = modResource::getControllerPath($modx);
        return $path.'staticresource/';
    }

    /**
     * Use this in your extended Resource class to display the text for the context menu item, if showInContextMenu is
     * set to true.
     * @return array
     */
    public function getContextMenuText() {
        return array(
            'text_create' => $this->xpdo->lexicon('static_resource'),
            'text_create_here' => $this->xpdo->lexicon('static_resource_create_here'),
        );
    }

    /**
     * Use this in your extended Resource class to return a translatable name for the Resource Type.
     * @return string
     */
    public function getResourceTypeName() {
        return $this->xpdo->lexicon('static_resource');
    }

    /**
     * @return modMediaSource|null
     */
    private function getMediaSource()
    {
        $sourceId = (int)$this->xpdo->getOption('resource_static_source');
        if ($sourceId > 0) {
            // For security, we don't check if the source could be found, but if it's configured.
            // This makes sure that if static sources are to be restricted to media source access, that an incorrect
            // ACL configuration doesn't allow a user to bypass that restriction.
            $this->sourceConfigured = true;
            /** @var modMediaSource $source */
            $source = $this->xpdo->getObject('sources.modMediaSource', [
                'id' => $sourceId
            ]);
            if ($source && $source->initialize() && $source->getWorkingContext()) {
                $this->source = $source;
            }
        }
        return $this->source;
    }

    /**
     * Gets the name for the downloaded file for the resource
     *
     * @param modContentType $contentType
     * @return string
     */
    private function getAttachmentName(modContentType $contentType)
    {
        $ext = $contentType->getExtension();
        if ($alias= $this->get('uri')) {
            $name = basename($alias);
        }
        elseif ($this->get('alias')) {
            $name = $this->get('alias') . $ext;
        }
        elseif ($name = $this->get('pagetitle')) {
            $name = $this->cleanAlias($name) . $ext;
        }
        else {
            $name = 'download' . $ext;
        }

        return $name;
    }
}
