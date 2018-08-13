<?php


namespace Vziks\PageSpeed;

use Vziks\PageSpeed\Helpers\Colors;

/**
 * Class InfoGraphic
 * Project google-page-speed
 *
 * @author Anton Prokhorov <vziks@live.ru>
 */
class InfoGraphic
{

    /**
     * @var array
     */
    private $infoGraphicArray;
    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $score;


    /**
     * InfoGraphic constructor.
     * @param $infoGraphicArray
     * @param $stategy
     * @param int $score
     */
    public function __construct($infoGraphicArray, $stategy, $score = 70)
    {
        $this->infoGraphicArray = $infoGraphicArray;
        $this->strategy = $stategy;
        $this->score = $score;
    }


    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->infoGraphicArray['ruleGroups']['SPEED']['score'];
    }

    public function getColoredInfographic()
    {
        $console = new Colors();

        echo "\n\n" . 'Strategy ' . ucfirst($this->strategy) . ' optimization is: ' .
            ($this->getScore() < $this->score ?
                $console->getColoredString($this->getScore(), 'yellow') : $console->getColoredString($this->getScore(), 'green')
            ) . '/100' . "\n\n";

        foreach ($this->infoGraphicArray['loadingExperience']['metrics']['FIRST_CONTENTFUL_PAINT_MS']['distributions'] as $proportion) {
            $fcpCharts[] = intval(round($proportion['proportion'], 1, PHP_ROUND_HALF_UP) * 100);
        }

        $this->getFormatString($fcpCharts, 'FCP');

        foreach ($this->infoGraphicArray['loadingExperience']['metrics']['DOM_CONTENT_LOADED_EVENT_FIRED_MS']['distributions'] as $proportion) {
            $dclCharts[] = intval(round($proportion['proportion'], 1, PHP_ROUND_HALF_UP) * 100);
        }

        $this->getFormatString($dclCharts, 'DCL');

        if ($this->getScore() < $this->score) {
            echo "\n\n" . $console->getColoredString(sprintf('Threshold of %s not met with score of %s', $this->score, $this->getScore()), 'red', 'black', true);
            exit(1);
        }
        exit(0);
    }


    /**
     * @param $charts
     * @param $name
     */
    public function getFormatString($charts, $name)
    {
        $console = new Colors();

        $arrayColors = [
            '0' => 'green',
            '1' => 'yellow',
            '2' => 'red',
        ];
        $outputStrConsole = '';
        $outputStr = $name . ': ';

        foreach ($charts as $key => $chart) {
            for ($i = 0; $i < $chart; $i++) {
                $outputStr .= '|';
            }
            $outputStrConsole .= $console->getColoredString($outputStr, $arrayColors[$key], 'black');
            $outputStr = '';
        }

        echo $outputStrConsole . "\n";
    }
}
