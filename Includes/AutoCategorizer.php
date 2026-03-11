<?php

class AutoCategorizer {
    private $stopWords = [
        'a', 'an', 'the', 'and', 'or', 'but', 'if', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don', 'should', 'now', 'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing'
    ];

    /**
     * Clean and tokenize text into a bag of words (frequency map)
     */
    private function tokenize($text) {
        // Lowercase and remove punctuation
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9 ]/', '', $text);
        
        // Split into words
        $words = explode(' ', $text);
        
        $bag = [];
        foreach ($words as $word) {
            $word = trim($word);
            // Skip empty strings and stop words
            if (empty($word) || in_array($word, $this->stopWords)) {
                continue;
            }
            
            if (isset($bag[$word])) {
                $bag[$word]++;
            } else {
                $bag[$word] = 1;
            }
        }
        
        return $bag;
    }

    /**
     * Calculate Cosine Similarity between two bags of words
     */
    public function getCosineSimilarity($text1, $text2) {
        $bag1 = $this->tokenize($text1);
        $bag2 = $this->tokenize($text2);
        
        if (empty($bag1) || empty($bag2)) {
            return 0;
        }

        $allWords = array_unique(array_merge(array_keys($bag1), array_keys($bag2)));
        
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        foreach ($allWords as $word) {
            $valA = isset($bag1[$word]) ? $bag1[$word] : 0;
            $valB = isset($bag2[$word]) ? $bag2[$word] : 0;
            
            $dotProduct += ($valA * $valB);
            $normA += ($valA * $valA);
            $normB += ($valB * $valB);
        }
        
        if ($normA == 0 || $normB == 0) {
            return 0;
        }
        
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Find the best matching category for a given complaint description
     */
    public function suggestCategory($description, $categories) {
        $bestScore = -1;
        $bestCategoryId = null;
        
        foreach ($categories as $cat) {
            // Compare complaint description with category name AND description for better accuracy
            $catText = $cat['category_name'] . ' ' . $cat['description'];
            $score = $this->getCosineSimilarity($description, $catText);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCategoryId = $cat['category_id'];
            }
        }
        
        // Return best match if score is higher than a small threshold
        return ($bestScore > 0.05) ? $bestCategoryId : null;
    }
}
