<?php

namespace App\Http\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    public function getFilters() : array
    {
        return [
            new TwigFilter( 'date_format', [$this, 'dateFormat'] ),
            new TwigFilter( 'price', [$this, 'formatPrice'] ),
        ];
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction( 'calculate_duration', [$this, 'calculateDuration'] ),
        ];
    }

    public function formatPrice( int $number, int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ' ' ) : string
    {
        $price = number_format( $number, $decimals, $decimalSeparator, $thousandsSeparator );
        return $price . ' €';
    }

    public function dateFormat(\DateTimeInterface $date, string $format = 'd/m/Y H:i') : string
    {
        // Créer un formateur de date avec la localisation française
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $date->getTimezone(),
            \IntlDateFormatter::GREGORIAN
        );

        // Obtenir la partie date
        $formattedDate = $formatter->format($date);

        // Compléter avec l'heure si nécessaire
        if (strpos($format, 'H:i') !== false) {
            $formattedDate .= ' à ' . $date->format('H:i');
        }

        return $formattedDate;
    }

    public function calculateDuration( \DateTimeInterface $start, \DateTimeInterface $end ) : string
    {
        $duration = $start->diff( $end );
        $hours = $duration->h;
        $minutes = $duration->i;

        if ( $hours > 0 ) {
            $hours = $hours . ' h ';
        } else {
            $hours = '';
        }

        if ( $minutes > 0 ) {
            $minutes = $minutes . ' min';
        } else {
            $minutes = '';
        }

        return $hours . $minutes;
    }
}