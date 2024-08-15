<?php

namespace App\Http\Twig;

use App\Helper\TimeHelper;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigTimeExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return [
            new TwigFunction('event_duration', [$this, 'eventDuration']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('duration', $this->duration(...) ),
            new TwigFilter('ago', $this->ago(...), ['is_safe' => ['html']]),
            new TwigFilter('countdown', $this->countdown(...), ['is_safe' => ['html']]),
            new TwigFilter('duration_short', $this->shortDuration(...), ['is_safe' => ['html']]),
        ];
    }

    public function eventDuration(DateTimeInterface $start, DateTimeInterface $end): string
    {
        // Créer un formateur de date avec la localisation française
        $formatterDate = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE
        );

        $formatterDateTime = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::SHORT
        );

        // Si les dates sont le même jour, retournez comme : le 12 mai 2021 de 10:00 à 12:00
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return sprintf(
                '%s, %s à %s',
                $formatterDate->format($start),
                $start->format('H:i'),
                $end->format('H:i')
            );
        }

        // Si c'est la même année, retournez comme : du 12 mai au 13 mai 2021
        if ($start->format('Y') === $end->format('Y')) {
            return sprintf(
                'Du %s au %s',
                $formatterDate->format($start),
                $formatterDate->format($end)
            );
        }

        // Sinon, retournez avec date et heure complète
        return sprintf(
            'Du %s à %s au %s à %s',
            $formatterDateTime->format($start),
            $start->format('H:i'),
            $formatterDateTime->format($end),
            $end->format('H:i')
        );
    }

    public function duration(int $duration): string
    {
        return TimeHelper::duration($duration);
    }

    /**
     * Renvoie une durée au format court hh:mm:ss.
     */
    public function shortDuration(int $duration): string
    {
        $minutes = floor($duration / 60);
        $seconds = $duration - $minutes * 60;
        $times = [$minutes, $seconds];
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes - ($hours * 60);
            $times = [$hours, $minutes, $seconds];
        }

        return implode(':', array_map(
            fn (int|float $duration) => str_pad(strval($duration), 2, '0', STR_PAD_LEFT),
            $times
        ));
    }

    public function ago(\DateTimeInterface $date, string $prefix = ''): string
    {
        $prefixAttribute = !empty($prefix) ? " prefix=\"{$prefix}\"" : '';

        return "<time-ago time=\"{$date->getTimestamp()}\"$prefixAttribute></time-ago>";
    }

    public function countdown(\DateTimeInterface $date): string
    {
        return "<time-countdown time=\"{$date->getTimestamp()}\"></time-countdown>";
    }
}