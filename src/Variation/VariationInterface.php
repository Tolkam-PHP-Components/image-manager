<?php declare(strict_types=1);

namespace Tolkam\ImageManager\Variation;

interface VariationInterface
{
    /**
     * @return string
     */
    public function getId(): string;
    
    /**
     * @return string
     */
    public function getExtension(): string;
    
    /**
     * @return int|null
     */
    public function getWidth(): ?int;
    
    /**
     * @return int|null
     */
    public function getHeight(): ?int;
    
    /**
     * @return int|null
     */
    public function getQuality(): ?int;
    
    /**
     * @return int|null
     */
    public function getSharpen(): ?int;
    
    /**
     * @return bool
     */
    public function crop(): bool;
}
