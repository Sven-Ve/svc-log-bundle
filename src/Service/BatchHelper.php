<?php

/*
 * This file is part of the SvcLog bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\LogBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Svc\LogBundle\Exception\DeleteAllLogsForbidden;
use Svc\LogBundle\Exception\LogExceptionInterface;
use Svc\LogBundle\Repository\SvcLogRepository;
use Svc\UtilBundle\Service\NetworkHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Batch helper to fill location info.
 *
 * @author Sven Vetter <dev@sv-systems.com>
 */
class BatchHelper
{
    public function __construct(
        private readonly bool $enableIPSaving,
        private readonly EntityManagerInterface $entityManager,
        private readonly SvcLogRepository $logRepo,
    ) {
    }

    /**
     * fill the location info, called in batch because timing.
     *
     * @throws LogExceptionInterface
     *
     * @return int count of successful locations set
     */
    public function batchFillLocation(bool $force, SymfonyStyle $io): int
    {
        if (!$this->enableIPSaving) {
            throw new DeleteAllLogsForbidden();
        }

        $successCnt = 0;
        $counter = 0;
        if ($force) {
            $entries = $this->logRepo->findBy(['country' => '-']);
        } else {
            $entries = $this->logRepo->findBy(['country' => null]);
        }

        if (count($entries) == 0) {
            return 0;
        }

        $progressBar = new ProgressBar($io, count($entries));
        $progressBar->start();

        foreach ($entries as $entry) {
            $progressBar->advance();

            try {
                if (!$entry->getIp() or $entry->getIp() == '127.0.0.1') {
                    $entry->setCountry('-');
                    continue;
                }

                ++$counter;
                if ($counter == 100) {
                    $io->writeln(' Sleep 70 seconds because api limit on http://www.geoplugin.net (' . $successCnt . ' countries found).');
                    $this->entityManager->flush();
                    sleep(70);
                    $counter = 0;
                }

                $location = NetworkHelper::getLocationInfoByIp($entry->getIp());
                if ($location['country']) {
                    $entry->setCountry($location['country']);
                    $entry->setCity($location['city']);
                    ++$successCnt;
                } else {
                    $entry->setCountry('-');
                }
            } catch (\Exception) {
                $entry->setCountry('-');
            }
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Cannot save data: ' . $e->getMessage());
        }

        $progressBar->finish();

        return $successCnt;
    }
}
