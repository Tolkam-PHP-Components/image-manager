<?php declare(strict_types=1);

namespace Tolkam\ImageManager;

use Tolkam\ImageManager\Variation\VariationManager;
use Tolkam\UriGenerator\GeneratorStrategyInterface;
use Tolkam\UriGenerator\Strategy\Traits\PathJoinerTrait;

class UriGeneratorVariationStrategy implements GeneratorStrategyInterface
{
    use PathJoinerTrait;
    
    /**
     * @var VariationManager
     */
    protected VariationManager $variationManager;
    
    /**
     * @var string
     */
    protected string $variationAlias;
    
    /**
     * @var array
     */
    private array $options = [
        'prefix' => '',
        'glue' => '/',
    ];
    
    /**
     * @param VariationManager $variationManager
     * @param string           $variationAlias
     * @param array            $options
     */
    public function __construct(
        VariationManager $variationManager,
        string $variationAlias,
        array $options = []
    ) {
        $this->variationManager = $variationManager;
        $this->variationAlias = $variationAlias;
        $this->options = array_replace($this->options, $options);
    }
    
    /**
     * @inheritDoc
     */
    public function apply(string $resourceName): string
    {
        $resourceName = $this->variationManager->getVFL($resourceName, $this->variationAlias);
        
        return $this->join(
            [(string) $this->options['prefix'], $resourceName],
            $this->options['glue']
        );
    }
}
