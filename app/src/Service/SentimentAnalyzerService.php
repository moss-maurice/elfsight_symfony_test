<?php

namespace App\Service;

use Sentiment\Analyzer;

readonly final class SentimentAnalyzerService
{
    private readonly Analyzer $analyzer;

    public function __construct()
    {
        $this->analyzer = new Analyzer();
    }

    public function getSentiment(string $comment): float
    {
        $sentimentData = $this->matchSentiment($comment);

        // Так как sentiment analyze даёт значения от -1 до 1, то нам необходимо их преобразовать в значение от 0 до 1.
        // Математика простая - к значению compound прибавляем 1 и делим на 2.
        if (isset($sentimentData['compound'])) {
            return round((floatval($sentimentData['compound']) + 1) / 2, 3);
        }

        // На всякий случай, вернем нейтральный сентимент, если анализ не удался
        return floatval(0.5);
    }

    protected function matchSentiment(string $comment): array
    {
        return $this->analyzer->getSentiment($comment);
    }
}
