# Statistics


## Data grouped by country (e.q. for ChartJS)

### Option 1

worked directly with [symfony/ux-chartjs](https://github.com/symfony/ux-chartjs)

```php
use Svc\LogBundle\Service\LogStatistics;

/**
   * format counts/country for symfony/ux-chartjs
   *
   * @param integer $sourceID
   * @param integer|null $sourceType (Default 0)
   * @param integer|null $logLevel (Default DATA)
   * @param integer|null $maxEntries (Default 5)
   * @return array
   * @throws LogExceptionInterface
   */
  public function getCountriesForChartJS(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA, ?int $maxEntries = 5): array
```

*Result:* (could directly used as parameter for twig, see symfony/ux-chartjs documentation)

```
^ array:2 [▼
  "labels" => array:3 [▼
    0 => "?"
    1 => "DE"
    2 => "CH"
  ]
  "datasets" => array:1 [▼
    0 => array:1 [▼
      "data" => array:3 [▼
        0 => 5
        1 => 2
        2 => 1
      ]
    ]
  ]
]
```

### Option 2

worked with [Chart.js](https://www.chartjs.org/)

```php
use Svc\LogBundle\Service\LogStatistics;

 /**
   * format counts/country as array for direct chart.js integration per yarn
   * 
   * @param integer $sourceID
   * @param integer|null $sourceType
   * @param integer|null $logLevel
   * @param integer|null $maxEntries
   * @return array
   * @throws LogExceptionInterface
   */
  public function getCountriesForChartJS1(int $sourceID, ?int $sourceType = 0, ?int $logLevel = EventLog::LEVEL_DATA, ?int $maxEntries = 5): array
```

*Result:* (must transformed in a JavaScript array)

```
^ array:2 [▼
  "labels" => "?|DE|CH"
  "data" => "5|2|1"
]
```