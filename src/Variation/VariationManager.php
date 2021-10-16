<?php declare(strict_types=1);

namespace Tolkam\ImageManager\Variation;

class VariationManager
{
    const PATTERN_DEFAULT = '%filename%__%id%.%ext%';
    
    /**
     * Extensions to not replace with variation extension
     * @var string[]
     */
    protected array $passThroughFormats = [
        'gif',
        'svg',
    ];
    
    /**
     * @var VariationInterface[]
     */
    protected array $variations = [];
    
    /**
     * @var string
     */
    protected string $pattern;
    
    /**
     * @var array
     */
    private array $idAliasCache = [];
    
    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = self::PATTERN_DEFAULT)
    {
        $this->pattern = $pattern;
    }
    
    /**
     * Gets the pass-through formats
     *
     * @return string[]
     */
    public function getPassThroughFormats(): array
    {
        return $this->passThroughFormats;
    }
    
    /**
     * Sets the pass-through formats
     *
     * @param string[] $formats
     *
     * @return self
     */
    public function setPassThroughFormats(array $formats): self
    {
        $this->passThroughFormats = $formats;
        
        return $this;
    }
    
    /**
     * @param VariationInterface $variation
     * @param string             $alias
     *
     * @return VariationManager
     * @throws VariationManagerException
     */
    public function register(VariationInterface $variation, string $alias): self
    {
        if (isset($this->variations[$alias])) {
            throw new VariationManagerException(sprintf(
                'Variation "%s" already registered',
                $alias
            ));
        }
        
        $id = $variation->getId();
        if (!$this->isValidId($id)) {
            throw new VariationManagerException(sprintf(
                'Invalid variation id "%s"',
                $id
            ));
        }
        
        $this->variations[$alias] = $variation;
        
        return $this;
    }
    
    /**
     * @param string $alias
     *
     * @return VariationInterface
     * @throws VariationManagerException
     */
    public function get(string $alias): VariationInterface
    {
        if (!isset($this->variations[$alias])) {
            throw new VariationManagerException(sprintf(
                'Variation "%s" is not registered',
                $alias
            ));
        }
        
        return $this->variations[$alias];
    }
    
    /**
     * Gets all registered variations
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->variations;
    }
    
    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }
    
    /**
     * Applies current pattern to provided placeholder values
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function applyPattern(array $placeholders): string
    {
        return strtr($this->getPattern(), $placeholders);
    }
    
    /**
     * Gets variation filename
     *
     * @param string                    $sourceFileName
     * @param VariationInterface|string $variation
     *
     * @return string
     * @throws VariationManagerException
     */
    public function getVFL(string $sourceFileName, $variation): string
    {
        $isString = is_string($variation);
        if (!$isString && !$variation instanceof VariationInterface) {
            throw new VariationManagerException(sprintf(
                'Variation must be string alias or implement %s',
                VariationInterface::class,
            ));
        }
        
        $variation = !$isString ? $variation : $this->get($variation);
        
        $pathInfo = pathinfo($sourceFileName);
        $filename = $pathInfo['filename'];
        $sourceExt = $pathInfo['extension'] ?? '';
        
        $newName = $this->applyPattern([
            '%id%' => $variation->getId(),
            '%filename%' => $filename,
            '%ext%' => in_array($sourceExt, $this->passThroughFormats)
                ? $sourceExt
                : $variation->getExtension(),
        ]);
        
        // get everything before filename
        $prefix = mb_substr($sourceFileName, 0, mb_strpos($sourceFileName, $filename));
        
        return $prefix . strtr($filename, [$filename => $newName]);
    }
    
    /**
     * Finds registered alias from previously generated variation filename
     *
     * @param string $variationFilename
     *
     * @return string|null
     */
    public function aliasFromVFL(string $variationFilename): ?string
    {
        if (!$parsed = $this->parse($variationFilename)) {
            return null;
        }
        
        return $this->getAliasById($parsed['id']);
    }
    
    /**
     * Builds original filename from previously generated variation filename
     *
     * @param string $variationFilename
     *
     * @return string|null
     */
    public function filenameFromVFL(string $variationFilename): ?string
    {
        if (!$parsed = $this->parse($variationFilename)) {
            return null;
        }
        
        return $parsed['filename'];
    }
    
    /**
     * @param string $id
     *
     * @return string|null
     */
    private function getAliasById(string $id): ?string
    {
        if (isset($this->idAliasCache[$id])) {
            return $this->idAliasCache[$id];
        }
        
        foreach ($this->variations as $alias => $variation) {
            if ($variation->getId() === $id) {
                $this->idAliasCache[$id] = $alias;
                
                return $alias;
            }
        }
        
        return null;
    }
    
    /**
     * Parses variation filename
     *
     * @param string $variationFilename
     *
     * @return array|null
     */
    private function parse(string $variationFilename): ?array
    {
        $regex = strtr(preg_quote($this->pattern), [
            '%id%' => '(?P<id>.+?)',
            '%filename%' => '(?P<filename>.+?)',
            '%ext%' => '(?P<ext>.+?)',
        ]);
        
        if (!preg_match('~^' . $regex . '$~', $variationFilename, $matches)) {
            return null;
        }
        
        return $matches;
    }
    
    /**
     * Checks if variation id is valid
     *
     * @param string $name
     *
     * @return false|int
     */
    private function isValidId(string $name)
    {
        return preg_match('~^[A-Za-z0-9x]{1,30}$~', $name);
    }
}
