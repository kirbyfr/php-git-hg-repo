<?php
namespace PHPHg;


use PHPHg\Command;
use PHPHg\Configuration;

/**
 * Simple PHP wrapper for Hg repository
 *
 * @link      http://github.com/ornicar/php-git-repo
 * @version   1.3.0
 * @author    Blondeau Gabriel <blondeau.gabriel at gmail dot com>
 * @license   
 *
 * Documentation: http://github.com/ornicar/php-git-repo/blob/master/README.markdown
 * Tickets:       http://github.com/ornicar/php-git-repo/issues
 */
class Repository
{
    /**
     * @var string  local repository directory
     */
    protected $dir;

    protected $dateFormat = 'iso';
    protected $logFormat = '"%H|%T|%an|%ae|%ad|%cn|%ce|%cd|%s"';

    /**
     * @var boolean Whether to enable debug mode or not
     * When debug mode is on, commands and their output are displayed
     */
    protected $debug;

    /**
     * @var array of options
     */
    protected $options;

    protected static $defaultOptions = array(
        'command_class'   => 'Command', // class used to create a command
        'hg_executable'   => '/usr/bin/hg', // path of the executable on the server
        'file_config' => '/.hg/'
    );

    /**
     * Instanciate a new Git repository wrapper
     *
     * @param   string $dir real filesystem path of the repository
     * @param   boolean $debug
     * @param   array $options
     */
    public function __construct($dir, $debug = false, array $options = array())
    {
        $this->dir      = $dir;
        $this->debug    = $debug;
        $this->options  = array_merge(self::$defaultOptions, $options);

        $this->checkIsValidHgRepo();
    }

    
    /**
     * Get the configuration for current
     * @return Configuration
     */
    public function getConfiguration()
    {
      return new Configuration($this);
    }
    
    /**
     * Return the result of `hg log` formatted in a PHP array
     *
     * @return array list of commits and their properties
     **/
    public function getCommits($nbCommits = 10)
    {
        $output = $this->cmd(sprintf('log -l %d', $nbCommits));
        return $output;
    }

    /**
     * Return the result of `hg pull` formatted in a PHP array
     *
     * @return array list of commits and their properties
     **/
    public function pull($options = "")
    {
        $output = $this->cmd(sprintf('pull %d', $options));
        return $output;
    }
    /**
     * Return the result of `hg update` formatted in a PHP array
     *
     * @return array list of commits and their properties
     **/
    public function update($options = "")
    {
        $output = $this->cmd(sprintf('update %d', $options));
        return $output;
    }
    
    /**
     * Check if a directory is a valid Hg repository
     */
    public function checkIsValidHgRepo()
    {
        if(!file_exists($this->dir.'/.hg/hgrc')) {
            throw new InvalidHgRepositoryDirectoryException($this->dir.' is not a valid Hg repository');
        }
    }
    
    /**
     * Run any hg command, like "status" or "checkout -b mybranch origin/mybranch"
     *
     * @throws  RuntimeException
     * @param   string  $commandString
     * @return  string  $output
     */
    public function cmd($commandString)
    {
        // clean commands that begin with "git "
        $commandString = preg_replace('/^hg\s/', '', $commandString);

        $commandString = $this->options['hg_executable'].' '.$commandString;

        $command = new $this->options['command_class']($this->dir, $commandString, $this->debug);

        return $command->runHg();
    }

    /**
     * Get the repository directory
     *
     * @return  string  the repository directory
     */
    public function getDir()
    {
        return $this->dir;
    }
    
    /**
     * Get the repository directory
     *
     * @return  string  the repository directory
     */
    public function getFilleConfig()
    {
        return $this->options['file_config'];
    }
}

class InvalidHgRepositoryDirectoryException extends \InvalidArgumentException
{
}