<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;

/**
 * FAQ Collector - Fetches FAQ categories and questions from Amasty FAQ
 * without requiring customer context (HTTP context)
 *
 * Uses ObjectManager for optional Amasty module dependencies
 * to gracefully handle cases where Amasty FAQ module is not installed
 */
class FaqCollector
{
    public function __construct(
        private readonly Manager $moduleManager,
        private readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * Collect FAQ data for the LLMS.txt feed
     * Returns only enabled categories with their questions
     *
     * @param int $storeId
     * @return array Array of categories with questions
     */
    public function collect(int $storeId): array
    {
        // Check if Amasty FAQ module is installed and enabled
        if (!$this->moduleManager->isEnabled('Amasty_Faq')) {
            return [];
        }

        try {
            // Lazy load Amasty ConfigProvider using ObjectManager
            $configProvider = $this->objectManager->get(\Amasty\Faq\Model\ConfigProvider::class);

            if (!$configProvider->isEnabled()) {
                return [];
            }

            $faqData = [];
            $categories = $this->getCategories($storeId);

            foreach ($categories as $category) {
                $questions = $this->getCategoryQuestions($category, $storeId);

                if (empty($questions)) {
                    continue; // Skip categories with no questions
                }

                $categoryQuestions = [];
                foreach ($questions as $question) {
                    $categoryQuestions[] = [
                        'title' => $this->sanitize((string)$question->getTitle()),
                        'position' => (int)$question->getPosition(),
                        'answer' => $this->sanitize((string)$question->getAnswer())
                    ];
                }

                // Only add category if it has questions
                if (!empty($categoryQuestions)) {
                    $faqData[] = [
                        'category_title' => (string)$category->getTitle(),
                        'category_position' => (int)$category->getPosition(),
                        'questions' => $categoryQuestions
                    ];
                }
            }

            return $faqData;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get enabled FAQ categories for the store
     *
     * @param int $storeId
     * @return array
     */
    private function getCategories(int $storeId): array
    {
        try {
            $collectionFactory = $this->objectManager->get(
                \Amasty\Faq\Model\ResourceModel\Category\CollectionFactory::class
            );

            $collection = $collectionFactory->create();

            // Add store filter (includes default store and current store)
            $collection->addStoreFilter([Store::DEFAULT_STORE_ID, $storeId]);

            // Filter by enabled status
            $collection->addFieldToFilter('status', 1);

            // Sort by position ascending (default behavior)
            $collection->setOrder('position', 'ASC');

            return $collection->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get questions for a specific category
     *
     * @param object $category
     * @param int $storeId
     * @return array
     */
    private function getCategoryQuestions($category, int $storeId): array
    {
        try {
            $collectionFactory = $this->objectManager->get(
                CollectionFactory::class
            );

            $collection = $collectionFactory->create();

            // Filter by category
            $collection->addCategoryFilter($category->getCategoryId());

            // Filter by store (includes default store and current store)
            $collection->addStoreFilter([Store::DEFAULT_STORE_ID, $storeId]);

            // Filter by enabled status - only answered questions
            $collection->addFieldToFilter('status', 1); // Status::STATUS_ANSWERED

            // Filter by visibility - only public questions (not requiring login for LLMS data)
            $collection->addFieldToFilter('visibility', 1); // Visibility::VISIBILITY_PUBLIC

            // Sort by position ascending (default behavior)
            $collection->setOrder('position', 'ASC');

            return $collection->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Sanitize FAQ answer by removing all HTML markup and normalizing whitespace
     * Handles Page Builder content with style tags and data attributes
     *
     * @param string $html The raw answer content
     * @return string Sanitized text content
     */
    private function sanitize(string $html): string
    {
        // Remove style and script tags along with their content
        $sanitized = preg_replace(
            ['#<style[^>]*>.*?</style>#is', '#<script[^>]*>.*?</script>#is'],
            '',
            $html
        );

        // Remove all remaining HTML tags
        $sanitized = strip_tags($sanitized);

        // Decode HTML entities
        $sanitized = html_entity_decode($sanitized, ENT_QUOTES | ENT_HTML5);

        // Normalize whitespace: collapse multiple spaces/newlines and trim
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = trim($sanitized);

        return $sanitized;
    }
}

