<?php
declare(strict_types=1);

namespace Flancer32\Csp\Api\Data;

interface RuleSentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get csp_rule_sent list.
     * @return \Flancer32\Csp\Api\Data\RuleSentInterface[]
     */
    public function getItems();

    /**
     * Set csp_rule_id list.
     * @param \Flancer32\Csp\Api\Data\RuleSentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

