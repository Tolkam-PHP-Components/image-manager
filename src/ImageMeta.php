<?php declare(strict_types=1);

namespace Tolkam\ImageManager;

class ImageMeta
{
    /**
     * Low-quality preview
     *
     * @var string
     */
    protected string $lqp = '';
    
    /**
     * Average color
     *
     * @var array
     */
    protected array $avgColor = [0, 0, 0];
    
    /**
     * @var int
     */
    protected int $width = 0;
    
    /**
     * @var int
     */
    protected int $height = 0;
    
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
     * @return array
     */
    public function toArray(): array
    {
        $color = $this->avgColor;
        
        return [
            'lqp' => $this->lqp,
            // 'avgColor' => [
            //     'r' => $color[0],
            //     'g' => $color[1],
            //     'b' => $color[2],
            //     'hex' => sprintf('%02x%02x%02x', ...$color),
            // ],
            'avgColor' => sprintf('%02x%02x%02x', ...$color),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
