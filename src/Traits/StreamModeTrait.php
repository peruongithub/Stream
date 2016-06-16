<?php
/**
 * This file is part of the Trident package.
 *
 * Perederko Ruslan <perederko.ruslan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Trident\Component\Stream\Traits;

trait StreamModeTrait
{
    protected $mode;
    private $base_mode;
    private $plus_mode;
    private $flag_mode;

    /**
     * Returns the underlying mode
     *
     * @return string|null
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function isDefinedMode(){
        return null !== $this->mode;
    }

    /**
     * Constructor
     *
     * @param string $mode A stream mode as for the use of fopen()
     */
    protected function setMode($mode)
    {
        if(null !== $this->mode){
            throw new \LogicException('Can not re-define the stream mode');
        }
        $this->mode = $mode;

        $mode = substr($mode, 0, 3);
        $rest = substr($mode, 1);

        $this->base_mode = substr($mode, 0, 1);
        $this->plus_mode = false !== strpos($rest, '+');
        $this->flag_mode = trim($rest, '+');
    }

    /**
     * Indicates whether the mode allows to read
     *
     * @return bool
     */
    public function isReadable()
    {
        if ($this->plus_mode) {
            return true;
        }

        return 'r' === $this->base_mode;
    }

    /**
     * Indicates whether the mode allows to write
     *
     * @return bool
     */
    public function isWritable()
    {
        if ($this->plus_mode) {
            return true;
        }

        return 'r' !== $this->base_mode && $this->mode;
    }

    /**
     * Indicates whether the mode allows to open an existing file
     *
     * @return bool
     */
    public function isAllowOpenExistingFile()
    {
        return 'x' !== $this->base_mode && $this->mode;
    }

    /**
     * Indicates whether the mode allows to create a new file
     *
     * @return bool
     */
    public function isAllowCreateNewFile()
    {
        return 'r' !== $this->base_mode && $this->mode;
    }

    /**
     * Indicates whether the mode implies to delete the existing content of the
     * file when it already exists
     *
     * @return bool
     */
    public function impliesExistingContentDeletion()
    {
        return 'w' === $this->base_mode;
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the
     * beginning of the file
     *
     * @return bool
     */
    public function impliesPositioningCursorAtTheBeginning()
    {
        return 'a' !== $this->base_mode && $this->mode;
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the end of
     * the file
     *
     * @return bool
     */
    public function impliesPositioningCursorAtTheEnd()
    {
        return 'a' === $this->base_mode;
    }

    /**
     * Indicates whether the stream is in text mode
     *
     * @return bool
     */
    public function isText()
    {
        return false === $this->isBinary() && $this->mode;
    }

    /**
     * Indicates whether the stream is in binary mode
     *
     * @return bool
     */
    public function isBinary()
    {
        return 'b' === $this->flag_mode;
    }

    protected function detachMode(){
        $this->mode = $this->base_mode = $this->plus_mode = $this->flag_mode = null;
    }
}
