<?php

namespace App\Controller;

use App\Repository\CheckedHistoryRepository;
use App\Repository\CustomerCardRepository;
use App\Repository\TransferArrivalRepository;
use App\Repository\TransferVehicleArrivalRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class StatsController extends AbstractController
{
    #[Route('/stats/CheckHistory', name: 'app_stats_check_history')]
    public function index(CheckedHistoryRepository $checkedHistoryRepository, 
                          CustomerCardRepository $customerCardRepository,
                          TransferVehicleArrivalRepository $transferVehicleArrivalRepository,
                          ChartBuilderInterface $chartBuilder,
                          UserRepository $userRepository): Response
    {


        /* $numberTotalOfClients = count($customerCardRepository->findAll());
        $briefingsChecks = $customerCardRepository->findBy(['isChecked' => true]); 

        $numberTotalOfVehicleArrivals = count($transferVehicleArrivalRepository->findAll());
        $airportsChecks = $transferVehicleArrivalRepository->findBy(['isChecked' => true]); */

        $checkedHistory = $checkedHistoryRepository->findAll();
        // exemple simple sans repository
        $totalNumberAirport = count($checkedHistoryRepository->findBy(['type' => 1]));
        $totalCheckAirport = count($checkedHistoryRepository->findBy(['type' => 1, 'isChecked' => 1]));

        $totalNumberBriefing = count($checkedHistoryRepository->findBy(['type' => 2]));
        $totalCheckBriefing = count($checkedHistoryRepository->findBy(['type' => 2, 'isChecked' => 1]));
        
        $reps = $userRepository->findAll();

        /* dump($totalNumberAirport);
        dump($totalCheckAirport);
        dump($totalNumberBriefing);
        dump($totalCheckBriefing);
 */

        $airportPie = $chartBuilder->createChart(Chart::TYPE_PIE);

        $airportPie->setData([
            'labels' => ['Checked','Not Checked'],
            'datasets' => [
                [
                    'label' => 'Nombre de Clients',
                    'backgroundColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                    ],
                    'hoverOffset'=> 4,
                    // 'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [$totalCheckAirport, $totalNumberAirport-$totalCheckAirport],
                ],
            ],
        ]);

        $briefingPie = $chartBuilder->createChart(Chart::TYPE_PIE);

        $briefingPie->setData([
            'labels' => ['Checked','Not Checked'],
            'datasets' => [
                [
                    'label' => 'Nombre de Clients',
                    'backgroundColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                    ],
                    'hoverOffset'=> 4,
                    // 'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [$totalCheckBriefing, $totalNumberBriefing-$totalCheckBriefing],
                ],
            ],
        ]);


        return $this->render('stats/index.html.twig', [
            'checkedHistory' => $checkedHistory,
            'totalNumberAirport' => $totalNumberAirport,
            'totalCheckAirport' => $totalCheckAirport,
            'totalNumberBriefing' => $totalNumberBriefing ,
            'totalCheckBriefing' => $totalCheckBriefing,
            'airportPie' => $airportPie,
            'briefingPie' => $briefingPie,
            'reps' => $reps
        ]);
    }
    #[Route('/stats/example', name: 'app_stats_example')]
    public function statsExample(ChartBuilderInterface $chartBuilder): Response
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
                    'hoverOffset'=> 30,
                    //'borderColor' => 'rgb(255, 99, 132)',
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


        return $this->render('stats/stats_example.html.twig', [
            'chart' => $chart,
            'pie' => $pie
        ]);
    }
}
