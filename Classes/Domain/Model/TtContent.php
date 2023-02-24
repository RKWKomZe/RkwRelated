<?php

namespace RKW\RkwRelated\Domain\Model;
/**
 * TtContent
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContent extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var int
     */
    protected int $ctype = 0;


    /**
     * @var int
     */
    protected int $colpos = 0;


    /**
     * @var int
     */
    protected int $crdate = 0;


    /**
     * @var int
     */
    protected int $sysLanguageUid = 0;


    /**
     * @var string
     */
    protected string $header = '';


    /**
     * @var string
     */
    protected string $bodytext = '';


    /**
     * @var string
     */
    protected string $headerLink = '';


    /**
     * Returns the uid
     *
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }


    /**
     * Sets the uid
     *
     * @param int $uid
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }


    /**
     * Returns the pid
     *
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }


    /**
     * Sets the pid
     *
     * @param int $pid
     * @return void
     */
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }


    /**
     * Returns the ctype
     *
     * @return int
     */
    public function getCtype(): int
    {
        return $this->ctype;
    }


    /**
     * Sets the ctype
     *
     * @param int $ctype
     * @return void
     */
    public function setCtype(int $ctype): void
    {
        $this->ctype = $ctype;
    }


    /**
     * Returns the colpos
     *
     * @return int
     */
    public function getColpos(): int
    {
        return $this->colpos;
    }


    /**
     * Sets the colpos
     *
     * @param int $colpos
     * @return void
     */
    public function setColpos(int $colpos): void
    {
        $this->colpos = $colpos;
    }


    /**
     * Returns the crdate
     *
     * @return int
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }


    /**
     * Sets the crdate
     *
     * @param int $crdate
     * @return void
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }


    /**
     * Returns the sysLanguageUid
     *
     * @return int
     */
    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }


    /**
     * Sets the sysLanguageUid
     *
     * @param int $sysLanguageUid
     * @return void
     */
    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }


    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader(): string
    {
        return $this->header;
    }


    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader(string $header): void
    {
        $this->header = $header;
    }


    /**
     * Returns the bodytext
     *
     * @return string
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }


    /**
     * Sets the bodytext
     *
     * @param string $bodytext
     * @return void
     */
    public function setBodytext(string $bodytext): void
    {
        $this->bodytext = $bodytext;
    }


    /**
     * Returns the headerLink
     *
     * @return string
     */
    public function getHeaderLink(): string
    {
        return $this->headerLink;
    }


    /**
     * Sets the headerLink
     *
     * @param string $headerLink
     * @return void
     */
    public function setHeaderLink(string $headerLink): void
    {
        $this->headerLink = $headerLink;
    }
}
