<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
                [
                    'label' => 'My second dataset',
                    'backgroundColor' => 'rgb(100, 99, 132)',
                    'borderColor' => 'rgb(100, 99, 132)',
                    'data' => [10, 12, 50, 2, 22, 18, 23],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        $pie = $chartBuilder->createChart(Chart::TYPE_PIE);

        $pie->setData([
            'labels' => ['Benjamin','Sebastien','Fanny'],
            'datasets' => [
                [
                    'label' => 'Nombre de Clients',
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)'
                      ],
                    'hoverOffset'=> 4,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [300, 50, 100],
                ],
            ],
        ]);

/*         $pie->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]); */


        return $this->render('stats/index.html.twig', [
            'chart' => $chart,
            'pie' => $pie
        ]);
    }
}
