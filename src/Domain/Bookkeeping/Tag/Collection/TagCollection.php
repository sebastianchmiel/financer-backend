<?php

namespace App\Domain\Bookkeeping\Tag\Collection;

use App\Repository\Bookkeeping\Tag\TagRepository;
use App\Entity\Bookkeeping\Tag\Tag;

/**
 * Description of TagCollection
 *
 * @author Sebastian
 */
class TagCollection {
    
    /**
     * @var TagRepository
     */
    private $tagRepository;
    
    /**
     * @param TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository) {
        $this->tagRepository = $tagRepository;
    }
    
    /**
     * get all tags as array with all data
     * 
     * @return array
     */
    public function getAllAsArray() {
        $tags = [];
        $tagsDb = $this->tagRepository->findAll();
        
        /* @var $tag Tag */
        foreach ($tagsDb as $tag) {
            $tags[$tag->getId()] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'fontColor' => $tag->getFontColor(),
                'backgroundColor' => $tag->getBackgroundColor(),
                'includeInBalance' => $tag->getIncludeInBalance(),
                'includeInBalanceChart' => $tag->getIncludeInBalanceChart(),
                'includeInRealCost' => $tag->getIncludeInRealCost(),
                'settlementType' => $tag->getSettlementType(),
                'bankStatementPhrases' => $tag->getBankStatementPhrases(),
            ];
        }
        
        return $tags;
    }
}
