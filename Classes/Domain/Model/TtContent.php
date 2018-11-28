<?php

namespace RKW\RkwRelated\Domain\Model;
/**
 * TtContent
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContent extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * uid
     *
     * @var integer
     */
    protected $uid;

    /**
     * pid
     *
     * @var integer
     */
    protected $pid;

    /**
     * ctype
     *
     * @var integer
     */
    protected $ctype;

    /**
     * colpos
     *
     * @var integer
     */
    protected $colpos;

    /**
     * crdate
     *
     * @var integer
     */
    protected $crdate;

    /**
     * sysLanguageUid
     *
     * @var integer
     */
    protected $sysLanguageUid;

    /**
     * header
     *
     * @var string
     */
    protected $header;

    /**
     * bodytext
     *
     * @var string
     */
    protected $bodytext;

    /**
     * headerLink
     *
     * @var string
     */
    protected $headerLink;

    /**
     * Returns the uid
     *
     * @return integer $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the uid
     *
     * @param integer $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Returns the pid
     *
     * @return integer $pid
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Sets the pid
     *
     * @param integer $pid
     * @return void
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Returns the ctype
     *
     * @return integer $ctype
     */
    public function getCtype()
    {
        return $this->ctype;
    }

    /**
     * Sets the ctype
     *
     * @param integer $ctype
     * @return void
     */
    public function setCtype($ctype)
    {
        $this->ctype = $ctype;
    }

    /**
     * Returns the colpos
     *
     * @return integer $colpos
     */
    public function getColpos()
    {
        return $this->colpos;
    }

    /**
     * Sets the colpos
     *
     * @param integer $colpos
     * @return void
     */
    public function setColpos($colpos)
    {
        $this->colpos = $colpos;
    }

    /**
     * Returns the crdate
     *
     * @return integer $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param integer $crdate
     * @return void
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the sysLanguageUid
     *
     * @return integer $sysLanguageUid
     */
    public function getSysLanguageUid()
    {
        return $this->sysLanguageUid;
    }

    /**
     * Sets the sysLanguageUid
     *
     * @param integer $sysLanguageUid
     * @return void
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Returns the bodytext
     *
     * @return string $bodytext
     */
    public function getBodytext()
    {
        return $this->bodytext;
    }

    /**
     * Sets the bodytext
     *
     * @param string $bodytext
     * @return void
     */
    public function setBodytext($bodytext)
    {
        $this->bodytext = $bodytext;
    }

    /**
     * Returns the headerLink
     *
     * @return string $headerLink
     */
    public function getHeaderLink()
    {
        return $this->headerLink;
    }

    /**
     * Sets the headerLink
     *
     * @param string $headerLink
     * @return void
     */
    public function setHeaderLink($headerLink)
    {
        $this->headerLink = $headerLink;
    }
}