<?php declare(strict_types=1);

namespace Tolkam\ImageManager;

class ImageMeta
{
    /**
     * Low-quality preview
     *
     * @var string|null
     */
    protected ?string $lqp = null;
    
    /**
     * Average color
     *
     * @var array|null
     */
    protected ?array $avgColor = null;
    
    /**
     * @var int|null
     */
    protected ?int $width = null;
    
    /**
     * @var int|null
     */
    protected ?int $height = null;
    
    /**
     * Sets the low-quality preview
     *
     * @param string $lqp
     *
     * @return self
     */
    public function setLqp(string $lqp): self
    {
        $this->lqp = $lqp;
        
        return $this;
    }
    
    /**
     * Sets the average color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     *
     * @return self
     */
    public function setAverageColor(int $r, int $g, int $b): self
    {
        $this->avgColor = [$r, $g, $b];
        
        return $this;
    }
    
    /**
     * Sets the width
     *
     * @param int $width
     *
     * @return self
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;
        
        return $this;
    }
    
    /**
     * Sets the height
     *
     * @param int $height
     *
     * @return self
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;
        
        return $this;
    }
    
    /**
     * Gets the low-quality preview
     *
     * @return string|null
     */
    public function getLqp(): ?string
    {
        return $this->lqp;
    }
    
    /**
     * Gets the average color
     *
     * @return array|null
     */
    public function getAvgColor(): ?array
    {
        return $this->avgColor;
    }
    
    /**
     * Gets the width
     *
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }
    
    /**
     * Gets the height
     *
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        $color = $this->avgColor;
        
        return [
            'lqp' => $this->lqp,
            'avgColor' => $color ? sprintf('%02x%02x%02x', ...$color) : null,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
