<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_dpcalendar
 *
 * Layout alternativo "Scheda Evento" per DPCalendar.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \DigitalPeak\Component\DPCalendar\Site\View\Event\HtmlView $this */

$app = Factory::getApplication();
$app->getLanguage()->load('tpl_templateaccessibileperjoomla', JPATH_SITE);

$_parentTpl = \Joomla\CMS\Factory::getApplication()->getTemplate(true)->parent
    ?: \Joomla\CMS\Factory::getApplication()->getTemplate();
require_once JPATH_SITE . '/templates/' . $_parentTpl . '/helpers/TplAccessibileHelper.php';

$document = $app->getDocument();
$params   = $this->params;
$event    = $this->event;

// Funzione helper per generare embed video
$getVideoEmbed = function($url) {
    if (empty($url)) return '';
    
    // YouTube
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        return '<div class="ratio ratio-16x9 mb-3"><iframe src="https://www.youtube.com/embed/' . $match[1] . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>';
    }
    
    // Vimeo
    if (preg_match('%vimeo\.com/(?:video/)?([0-9]+)%i', $url, $match)) {
        return '<div class="ratio ratio-16x9 mb-3"><iframe src="https://player.vimeo.com/video/' . $match[1] . '" title="Vimeo video player" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
    }
    
    return $url;
};

// Risorse DPCalendar originali
if ($params->get('event_show_map', '1')
    && $event->locations
    && $params->get('event_show_location', '2') && $params->get('map_provider', 'openstreetmap') != 'none') {
    
    // Aumentiamo lo zoom di 8 livelli rispetto al parametro impostato (era +4, ora +8)
    $currentZoom = (int)$params->get('event_map_zoom', 4);
    $params->set('event_map_zoom', $currentZoom + 8);
    
    $this->layoutHelper->renderLayout('block.map', $this->displayData);
}

$this->dpdocument->loadStyleFile('dpcalendar/views/event/default.css');
$this->dpdocument->loadScriptFile('views/event/default.js');
$this->dpdocument->addStyle($params->get('event_custom_css', ''));


// Caricamento sottomodelli DPCalendar
$imageContent       = $this->loadTemplate('image_full');
$informationContent = $this->loadTemplate('information');
$headerContent      = $this->loadTemplate('header');
$ctaContent         = $this->loadTemplate('cta');
$descriptionContent = $this->loadTemplate('description');
$bookingFormContent = $this->loadTemplate('booking_form');
$bookingsContent    = $this->loadTemplate('bookings');
$seriesContent      = $this->loadTemplate('series');
$scheduleContent    = $this->loadTemplate('schedule');
$locationsContent   = $this->loadTemplate('locations');
$ticketsContent     = $this->loadTemplate('tickets');
$tagsContent        = $this->loadTemplate('tags');

// URL "Aggiungi al calendario" (Google / iCal-Outlook) — logica derivata da default_header.php
$googleUrl = '';
$icalUrl   = '';
try {
    $gStart = $this->dateHelper->getDate($event->start_date, $event->all_day);
    $gEnd   = $this->dateHelper->getDate($event->end_date, $event->all_day);
    $gFmt   = $event->all_day ? 'Ymd' : 'Ymd\THis';
    if ($event->all_day) {
        $gEnd->modify('+1 day');
    }

    // Indirizzo luogo (riusa la stessa composizione della sezione Luogo, niente dipendenza dal Geo model)
    $gLocationParts = [];
    if (!empty($event->locations)) {
        foreach ($event->locations as $loc) {
            $gLocationParts = array_filter([
                $loc->title ?? '',
                trim(($loc->street ?? '') . ' ' . ($loc->number ?? '')),
                $loc->zip ?? '',
                $loc->city ?? '',
                $loc->province ?? '',
            ]);
            break; // primo luogo
        }
    }

    $googleUrl  = 'https://www.google.com/calendar/render?action=TEMPLATE&text=' . urlencode((string) $event->title);
    $googleUrl .= '&dates=' . $gStart->format($gFmt, true) . '%2F' . $gEnd->format($gFmt, true);
    $googleUrl .= '&location=' . urlencode(implode(', ', $gLocationParts));
    $googleUrl .= '&details=' . urlencode((string) HTMLHelper::_('string.truncate', $event->description, 200));
    $googleUrl .= '&sf=true&output=xml';

    $icalUrl = $this->router->route('index.php?option=com_dpcalendar&view=event&format=raw&id=' . $event->id . '&calid=' . $event->catid);
} catch (\Throwable $e) {
    $googleUrl = '';
    $icalUrl   = '';
}

// URL evento — gate event_show_url (vedi default_information_url.php)
$showUrl = !empty($event->url) && $params->get('event_show_url', '1');

// Pulsante "Link evento" riutilizzabile (header meta + sezione "Collegamento esterno"), accessibile e con rel sicuro
$externalLinkButton = '';
if ($showUrl) {
    $externalLinkButton = '<a class="btn btn-outline-primary d-inline-flex align-items-center" '
        . 'href="' . htmlspecialchars((string) $event->url, ENT_QUOTES) . '" '
        . 'target="_blank" rel="noopener noreferrer nofollow" '
        . 'aria-label="' . htmlspecialchars(Text::_('TPL_ACCESSIBILE_EVENT_LINK') . ' ' . Text::_('TPL_ACCESSIBILE_NEW_WINDOW'), ENT_QUOTES) . '">'
        . '<span>' . Text::_('TPL_ACCESSIBILE_EVENT_LINK') . '</span>'
        . '<svg class="icon icon-primary icon-xs ms-2" aria-hidden="true"><use href="'
        . TplAccessibileHelper::spriteUrl('it-external-link') . '"></use></svg>'
        . '</a>';
}

// Mesi per il calendario verticale
$monthNames = [];
for ($m = 1; $m <= 12; $m++) {
    $monthNames[$m] = Text::_('TPL_ACCESSIBILE_MONTH_' . $m);
}

// Calcolo tempo di lettura
$wordCount = str_word_count(strip_tags($event->description));
$readingTime = ceil($wordCount / 200);
if ($readingTime < 1) $readingTime = 1;
?>

<div class="container pt-4">
    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2 mb-3">

            <?php
            // Categoria/calendario: non incluso nella query del model, caricato dalla Categories API
            $categoryTitle = '';
            try {
                $cat = \Joomla\CMS\Categories\Categories::getInstance('dpcalendar');
                if ($cat) {
                    $catItem = $cat->get($event->catid);
                    $categoryTitle = $catItem ? $catItem->title : '';
                }
            } catch (\Throwable $e) {}
            ?>
            <?php
            // Meta evento in testata: categoria + organizzatori + a cura di (ogni voce rispetta il proprio gate)
            $showHosts  = !empty($event->hosts) && $params->get('event_show_hosts', '1');
            $showAuthor = !empty($this->authorName) && $params->get('event_show_author', '1');
            ?>
            <?php if ($categoryTitle !== '') : ?>
                <p class="category-top mb-1 small">
                    <span class="category text-primary fw-semibold text-uppercase"><?php echo $this->escape($categoryTitle); ?></span>
                </p>
            <?php endif; ?>

            <h1 class="mb-2" data-element="event-title">
                <?php echo $this->escape($event->title); ?>
            </h1>

            <?php if ($event->state == 3) : ?>
                <div class="mb-3">
                    <span class="badge rounded-pill bg-danger">
                        <?php echo Text::_('MOD_DPCALENDAR_UPCOMING_CANCELED'); ?>
                    </span>
                </div>
            <?php endif; ?>

            <h2 class="visually-hidden"><?php echo Text::_('TPL_ACCESSIBILE_ARTICLE_DETAILS'); ?></h2>

            <h2 class="h4 py-2">
                <?php 
                $startDateStr = HTMLHelper::_('date', $event->start_date, Text::_('d F Y'));
                $endDateStr   = HTMLHelper::_('date', $event->end_date, Text::_('d F Y'));
                
                if ($startDateStr === $endDateStr) {
                    echo Text::sprintf('TPL_ACCESSIBILE_EVENT_ON_DATE', $startDateStr);
                } else {
                    echo Text::sprintf('TPL_ACCESSIBILE_EVENT_FROM_TO', $startDateStr, $endDateStr);
                }
                ?>
            </h2>

            <?php // Collegamento esterno: pulsante compatto (icona + testo) sotto la data ?>
            <?php if ($showUrl) : ?>
                <div class="mb-3">
                    <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center"
                       href="<?php echo $this->escape($event->url); ?>"
                       target="_blank" rel="noopener noreferrer nofollow"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_EVENT_LINK') . ' ' . Text::_('TPL_ACCESSIBILE_NEW_WINDOW'); ?>">
                        <svg class="icon icon-primary icon-xs me-2" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-external-link'); ?>"></use></svg>
                        <span><?php echo Text::_('TPL_ACCESSIBILE_EVENT_LINK'); ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->introText)) : ?>
                <div class="lead">
                    <?php echo $event->introText; ?>
                </div>
            <?php endif; ?>

        </div>

        <div class="col-lg-3 offset-lg-1 header-laterale align-self-end">

            <?php // Condividi ?>
            <div class="d-flex align-items-center mb-3">
                <span class="subtitle-small fw-semibold text-muted me-3 mb-0"><?php echo Text::_('TPL_ACCESSIBILE_SHARE'); ?>:</span>
                <div class="d-flex gap-2">
                    <a class="btn btn-action-icon d-flex align-items-center justify-content-center rounded"
                       href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(Uri::current()); ?>"
                       target="_blank" rel="noopener noreferrer"
                       title="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_FACEBOOK'); ?>"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_FACEBOOK'); ?>">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-facebook'); ?>"></use></svg>
                    </a>
                    <a class="btn btn-action-icon d-flex align-items-center justify-content-center rounded"
                       href="https://twitter.com/intent/tweet?text=<?php echo urlencode(Uri::current()); ?>"
                       target="_blank" rel="noopener noreferrer"
                       title="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_X'); ?>"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_X'); ?>">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-twitter'); ?>"></use></svg>
                    </a>
                    <a class="btn btn-action-icon d-flex align-items-center justify-content-center rounded"
                       href="https://api.whatsapp.com/send?text=<?php echo urlencode(Uri::current()); ?>"
                       target="_blank" rel="noopener noreferrer"
                       title="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_WHATSAPP'); ?>"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_SHARE_ON_WHATSAPP'); ?>">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-whatsapp'); ?>"></use></svg>
                    </a>
                </div>
            </div>

            <?php // Azioni ?>
            <div class="d-flex align-items-center mb-4">
                <span class="subtitle-small fw-semibold text-muted me-3 mb-0"><?php echo Text::_('TPL_ACCESSIBILE_ACTIONS'); ?>:</span>
                <div class="d-flex gap-2 align-items-center">
                    <?php if ($params->get('event_show_print', 1)) : ?>
                    <button class="btn btn-action-icon d-flex align-items-center justify-content-center rounded"
                            onclick="window.print();"
                            title="<?php echo Text::_('TPL_ACCESSIBILE_PRINT_PAGE'); ?>"
                            aria-label="<?php echo Text::_('TPL_ACCESSIBILE_PRINT_PAGE'); ?>">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-print'); ?>"></use></svg>
                    </button>
                    <?php endif; ?>
                    <a class="btn btn-action-icon d-flex align-items-center justify-content-center rounded"
                       href="mailto:?subject=<?php echo urlencode($event->title); ?>&amp;body=<?php echo urlencode(Uri::current()); ?>"
                       title="<?php echo Text::_('TPL_ACCESSIBILE_SEND_EMAIL'); ?>"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_SEND_EMAIL'); ?>">
                        <svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-mail'); ?>"></use></svg>
                    </a>
                </div>
            </div>

            <?php // Salva evento (esporta su calendario) ?>
            <?php if ($params->get('event_show_copy', '1') && ($googleUrl || $icalUrl)) : ?>
            <div class="mb-4">
                <span class="subtitle-small fw-semibold text-muted d-block mb-2"><?php echo Text::_('TPL_ACCESSIBILE_SAVE_EVENT'); ?>:</span>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php if ($googleUrl) : ?>
                    <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center"
                       href="<?php echo $googleUrl; ?>"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_ADD_TO_GOOGLE'); ?>">
                        <svg class="icon icon-primary icon-xs me-2" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-google'); ?>"></use></svg>
                        <span><?php echo Text::_('TPL_ACCESSIBILE_SAVE_EVENT_GOOGLE'); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if ($icalUrl) : ?>
                    <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center"
                       href="<?php echo $icalUrl; ?>"
                       aria-label="<?php echo Text::_('TPL_ACCESSIBILE_ADD_TO_ICAL'); ?>">
                        <svg class="icon icon-primary icon-xs me-2" aria-hidden="true"><use href="<?php echo TplAccessibileHelper::spriteUrl('it-download'); ?>"></use></svg>
                        <span><?php echo Text::_('TPL_ACCESSIBILE_SAVE_EVENT_OUTLOOK'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php // Argomenti (Tags) ?>
            <?php if ($tagsContent) : ?>
                <div class="mt-4 mb-4">
                    <span class="subtitle-small mb-2 d-block fw-semibold text-muted"><?php echo Text::_('TPL_ACCESSIBILE_TAGS'); ?></span>
                    <div class="comuni-tags"><?php echo $tagsContent; ?></div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php if ($imageContent) : ?>
<div class="container mb-4">
    <div class="row">
        <div class="col-12">
            <div class="it-page-section">
                <?php echo $imageContent; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <div class="row border-top border-light row-column-border row-column-menu-left mt-4 mt-lg-80 pb-lg-80 pb-40">

        <?php // Sidebar sinistra — indice di pagina ?>
        <aside class="col-lg-3 mb-4 border-col">
            <div class="cmp-navscroll sticky-top" role="region" aria-labelledby="accordion-title-evento">
                <nav class="navbar it-navscroll-wrapper navbar-expand-lg"
                     aria-label="<?php echo Text::_('TPL_ACCESSIBILE_PAGE_INDEX'); ?>"
                     data-bs-navscroll="">
                    <div class="navbar-custom" id="navbarNavEvento">
                        <div class="menu-wrapper">
                            <div class="link-list-wrapper">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <span class="accordion-header" id="accordion-title-evento">
                                            <button class="accordion-button pb-10 px-3 text-uppercase" type="button"
                                                    aria-controls="collapse-evento" aria-expanded="true"
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-evento">
                                                <?php echo Text::_('TPL_ACCESSIBILE_PAGE_INDEX'); ?>
                                            </button>
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar it-navscroll-progressbar" role="progressbar"
                                                 aria-valuemin="0" aria-valuemax="100"
                                                 style="width: 0%;"></div>
                                        </div>
                                        <div id="collapse-evento" class="accordion-collapse collapse show"
                                             role="region" aria-labelledby="accordion-title-evento">
                                            <div class="accordion-body">
                                                <ul class="link-list" data-element="page-index">
                                                    <?php if ($descriptionContent) : ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#descrizione">
                                                            <span><?php echo Text::_('TPL_ACCESSIBILE_SERVICE_DESCRIPTION'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#date-orari">
                                                            <span><?php echo Text::_('TPL_ACCESSIBILE_EVENT_DATE_ORARI'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php if ($showUrl) : ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#collegamento-esterno">
                                                            <span><?php echo Text::_('TPL_ACCESSIBILE_EVENT_URL'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php // Indice campi aggiuntivi ?>
                                                    <?php if (!empty($event->jcfields)) : ?>
                                                        <?php foreach ($event->jcfields as $field) : ?>
                                                            <?php if (!empty($field->value)) : ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#field-<?php echo $field->id; ?>">
                                                                        <span><?php echo $field->title; ?></span>
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>

                                                    <?php if ($locationsContent) : ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#luogo">
                                                            <span><?php echo Text::_('TPL_ACCESSIBILE_LOCATION'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($bookingFormContent || $ctaContent) : ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#prenotazione">
                                                            <span><?php echo Text::_('MOD_DPCALENDAR_UPCOMING_BOOK'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($seriesContent || $scheduleContent || $ticketsContent) : ?>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#informazioni">
                                                            <span><?php echo Text::_('TPL_ACCESSIBILE_SERVICE_INFO'); ?></span>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </aside>

        <?php // Contenuto principale ?>
        <div class="col-lg-9 it-page-sections-container border-light">

            <?php if (!empty($event->description)) : ?>
            <section class="it-page-section anchor-offset mb-30" id="descrizione">
                <h2 class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_SERVICE_DESCRIPTION'); ?></h2>
                <div class="richtext-wrapper lora">
                    <?php echo HTMLHelper::_('content.prepare', $event->description); ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // Organizzatori e A cura di — card complementari, prima di Date e orari ?>
            <?php if ($showHosts || $showAuthor) : ?>
            <div class="row mb-30">
                <?php if ($showHosts) : ?>
                <div class="col-12 col-md-6 mb-3">
                    <div class="card-wrapper card-teaser-wrapper h-100">
                        <div class="card shadow-sm rounded d-flex flex-row align-items-center h-100">
                            <div class="card-teaser-icon d-flex align-items-center flex-shrink-0 ps-3 pe-1 py-2">
                                <svg class="icon icon-primary icon-sm">
                                    <use href="<?php echo TplAccessibileHelper::spriteUrl('it-pa'); ?>"></use>
                                </svg>
                            </div>
                            <div class="card-body ps-2 py-2 pe-3">
                                <h3 class="card-title h6 text-uppercase text-muted mb-1"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_HOSTS'); ?></h3>
                                <div class="card-text">
                                    <p class="mb-0 fw-semibold"><?php
                                        $hostCount = is_countable($event->hosts) ? count($event->hosts) : 0;
                                        foreach ($event->hosts as $hIndex => $host) {
                                            if (!empty($host->link)) {
                                                echo '<a class="text-decoration-none" href="' . htmlspecialchars((string) $host->link, ENT_QUOTES) . '">' . $this->escape($host->name) . '</a>';
                                            } else {
                                                echo $this->escape($host->name);
                                            }
                                            if ($hIndex < $hostCount - 1) {
                                                echo ', ';
                                            }
                                        }
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($showAuthor) : ?>
                <div class="col-12 col-md-6 mb-3">
                    <div class="card-wrapper card-teaser-wrapper h-100">
                        <div class="card shadow-sm rounded d-flex flex-row align-items-center h-100">
                            <div class="card-teaser-icon d-flex align-items-center flex-shrink-0 ps-3 pe-1 py-2">
                                <svg class="icon icon-primary icon-sm">
                                    <use href="<?php echo TplAccessibileHelper::spriteUrl('it-user'); ?>"></use>
                                </svg>
                            </div>
                            <div class="card-body ps-2 py-2 pe-3">
                                <h3 class="card-title h6 text-uppercase text-muted mb-1"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_AUTHOR'); ?></h3>
                                <div class="card-text">
                                    <p class="mb-0 fw-semibold"><?php
                                        if (!empty($event->contact_link)) {
                                            echo '<a class="text-decoration-none" href="' . htmlspecialchars((string) $event->contact_link, ENT_QUOTES) . '">' . $this->authorName . ($this->avatar ?? '') . '</a>';
                                        } else {
                                            echo $this->authorName . ($this->avatar ?? '');
                                        }
                                    ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <section class="it-page-section anchor-offset mb-30" id="date-orari">
                <h2 class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_DATE_ORARI'); ?></h2>

                <div class="calendar-vertical mb-3">
                    <?php
                    $isAllDay = (bool)$event->all_day;
                    if ($isAllDay) {
                        $startDate = Factory::getDate($event->start_date);
                        $endDate   = Factory::getDate($event->end_date);
                    } else {
                        $tz        = new \DateTimeZone(Factory::getApplication()->get('offset', 'UTC'));
                        $startDate = Factory::getDate($event->start_date);
                        $startDate->setTimezone($tz);
                        $endDate   = Factory::getDate($event->end_date);
                        $endDate->setTimezone($tz);
                    }
                    $isSameDay = ($startDate->format('Y-m-d', true) === $endDate->format('Y-m-d', true));

                    $startDay      = $startDate->format('j', true);
                    $startMonthNum = (int)$startDate->format('n', true);
                    $startMonth    = $monthNames[$startMonthNum] ?? '';
                    $startYear     = $startDate->format('Y', true);
                    $startTime     = $startDate->format('H:i', true);
                    $endTime       = $endDate->format('H:i', true);

                    if (!$isSameDay) {
                        $endDay      = $endDate->format('j', true);
                        $endMonthNum = (int)$endDate->format('n', true);
                        $endMonth    = $monthNames[$endMonthNum] ?? '';
                        $endYear     = $endDate->format('Y', true);
                    }
                    ?>

                    <?php if ($isSameDay) : ?>
                    <div class="calendar-date">
                        <div class="calendar-date-day text-center">
                            <small class="calendar-date-day__year"><?php echo $startYear; ?></small>
                            <span class="title-xxlarge-regular d-flex justify-content-center"><?php echo $startDay; ?></span>
                            <small class="calendar-date-day__month"><?php echo $startMonth; ?></small>
                        </div>
                        <div class="calendar-date-description rounded">
                            <div class="calendar-date-description-content">
                                <?php if ($isAllDay) : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_ALL_DAY'); ?></h3>
                                <?php else : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::sprintf('TPL_ACCESSIBILE_EVENT_TIME_RANGE', '<strong>' . $startTime . '</strong>', '<strong>' . $endTime . '</strong>'); ?></h3>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="calendar-date mb-3">
                        <div class="calendar-date-day text-center">
                            <small class="calendar-date-day__year"><?php echo $startYear; ?></small>
                            <span class="title-xxlarge-regular d-flex justify-content-center"><?php echo $startDay; ?></span>
                            <small class="calendar-date-day__month"><?php echo $startMonth; ?></small>
                        </div>
                        <div class="calendar-date-description rounded">
                            <div class="calendar-date-description-content">
                                <?php if ($isAllDay) : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_ALL_DAY'); ?></h3>
                                <?php else : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::sprintf('TPL_ACCESSIBILE_EVENT_START_TIME', '<strong>' . $startTime . '</strong>'); ?></h3>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="calendar-date">
                        <div class="calendar-date-day text-center">
                            <small class="calendar-date-day__year"><?php echo $endYear; ?></small>
                            <span class="title-xxlarge-regular d-flex justify-content-center"><?php echo $endDay; ?></span>
                            <small class="calendar-date-day__month"><?php echo $endMonth; ?></small>
                        </div>
                        <div class="calendar-date-description rounded">
                            <div class="calendar-date-description-content">
                                <?php if ($isAllDay) : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_ALL_DAY'); ?></h3>
                                <?php else : ?>
                                    <h3 class="title-medium-2 mb-0"><?php echo Text::sprintf('TPL_ACCESSIBILE_EVENT_END_TIME', '<strong>' . $endTime . '</strong>'); ?></h3>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php // Ricorrenza (rrule) ?>
                <?php if (!empty($event->rrule) && method_exists($this->dateHelper, 'transformRRuleToString')) : ?>
                    <?php
                    $rruleString = '';
                    try {
                        $rruleString = (string) $this->dateHelper->transformRRuleToString(
                            $event->rrule,
                            $event->start_date,
                            $event->exdates ?? null
                        );
                    } catch (\Throwable $e) {
                        $rruleString = '';
                    }
                    ?>
                    <?php if ($rruleString !== '') : ?>
                        <p class="mb-0">
                            <span class="fw-semibold"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_RECURRENCE'); ?>:</span>
                            <?php echo nl2br($this->escape($rruleString)); ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <?php // Collegamento esterno ?>
            <?php if ($showUrl) : ?>
            <section class="it-page-section anchor-offset mb-30" id="collegamento-esterno">
                <h2 class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_URL'); ?></h2>
                <p class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_EVENT_EXTERNAL_LINK_INTRO'); ?></p>
                <?php echo $externalLinkButton; ?>
            </section>
            <?php endif; ?>

            <?php // Sezione Campi Aggiuntivi ?>
            <?php if (!empty($event->jcfields)) : ?>
                <?php foreach ($event->jcfields as $field) : ?>
                    <?php if (!empty($field->value)) : ?>
                        <section class="it-page-section anchor-offset mb-30" id="field-<?php echo $field->id; ?>">
                            <h2 class="mb-3"><?php echo $field->title; ?></h2>
                            
                            <?php if ($field->type === 'subform' && !empty($field->subform_rows)) : ?>
                                <?php // Render speciale per Gallery (Subform) ?>
                                <div class="it-carousel-wrapper it-carousel-landscape-abstract-three-cols splide" data-bs-carousel-splide>
                                    <div class="splide__track">
                                        <ul class="splide__list it-carousel-all">
                                            <?php foreach ($field->subform_rows as $row) : ?>
                                                <?php 
                                                // Estraiamo l'immagine e il testo dal row del subform
                                                // Nel dump vediamo che c'è un campo 'immagine' (field26)
                                                $imageData = null;
                                                foreach ($row as $rowField) {
                                                    if (isset($rowField->rawvalue) && is_array($rowField->rawvalue)) {
                                                        $imageData = $rowField->rawvalue;
                                                        break;
                                                    }
                                                }
                                                
                                                if ($imageData) : 
                                                    $imgSrc = $imageData['imagefile'] ?? '';
                                                    // Pulizia eventuale hash di Joomla dal path immagine
                                                    if (strpos($imgSrc, '#') !== false) {
                                                        $imgSrc = explode('#', $imgSrc)[0];
                                                    }
                                                    $altText = $imageData['alt_text'] ?? '';
                                                ?>
                                                <li class="splide__slide">
                                                    <div class="it-single-slide-wrapper">
                                                        <figure>
                                                            <img src="<?php echo Uri::root() . $imgSrc; ?>" alt="<?php echo $this->escape($altText); ?>" class="img-fluid">
                                                            <?php if ($altText) : ?>
                                                                <figcaption class="figure-caption mt-2"><?php echo $this->escape($altText); ?></figcaption>
                                                            <?php endif; ?>
                                                        </figure>
                                                    </div>
                                                </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="richtext-wrapper lora">
                                    <?php 
                                    // Se il valore è un URL video, genera l'embed, altrimenti stampa il valore normale
                                    $processedValue = $getVideoEmbed($field->value);
                                    echo ($processedValue !== $field->value) ? $processedValue : $field->value; 
                                    ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($event->locations)) : ?>
            <section class="it-page-section anchor-offset mb-30" id="luogo">
                <h2 class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_LOCATION'); ?></h2>
                
                <?php foreach ($event->locations as $loc) : ?>
                    <div class="card-wrapper card-teaser-wrapper">
                        <div class="card shadow mt-3 rounded d-flex flex-row align-items-center">
                            <div class="card-teaser-icon p-3">
                                <svg class="icon icon-primary">
                                    <use href="<?php echo TplAccessibileHelper::spriteUrl('it-pin'); ?>"></use>
                                </svg>
                            </div>
                            <div class="card-body ps-0">
                                <h3 class="card-title h5 mb-0">
                                    <a class="text-decoration-none" href="<?php echo Route::_('index.php?option=com_dpcalendar&view=location&id=' . $loc->id); ?>">
                                        <?php echo $this->escape($loc->title); ?>
                                    </a>
                                </h3>
                                <div class="card-text">
                                    <p class="mb-0">
                                        <?php 
                                        $addressParts = array_filter([
                                            trim(($loc->street ?? '') . ' ' . ($loc->number ?? '')),
                                            $loc->zip ?? '',
                                            $loc->city ?? '',
                                            $loc->province ?? ''
                                        ]);
                                        echo $this->escape(implode(', ', $addressParts));
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($params->get('event_show_map', '1')) : ?>
                    <div class="map-wrapper map-column mt-4">
                        <div class="dp-locations-map-only">
                            <?php echo $this->loadTemplate('locations'); ?>
                        </div>
                    </div>
                    <script>
                        /* Leaflet calcola le dimensioni all'init: dopo che il layout è a larghezza piena
                           forziamo un ricalcolo (invalidateSize via evento resize) così la mappa riempie il box. */
                        (function () {
                            var refit = function () { window.dispatchEvent(new Event('resize')); };
                            var run = function () { setTimeout(refit, 100); setTimeout(refit, 500); };
                            if (document.readyState === 'complete') {
                                run();
                            } else {
                                window.addEventListener('load', run);
                            }
                        })();
                    </script>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <?php if ($bookingFormContent || $ctaContent || $bookingsContent) : ?>
            <section class="it-page-section anchor-offset mb-30 has-bg-grey p-4" id="prenotazione">
                <h2 class="mb-3"><?php echo Text::_('MOD_DPCALENDAR_UPCOMING_BOOK'); ?></h2>
                <div class="richtext-wrapper lora">
                    <?php echo $ctaContent; ?>
                    <?php echo $bookingFormContent; ?>
                    <?php echo $bookingsContent; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($seriesContent || $scheduleContent || $ticketsContent) : ?>
            <section class="it-page-section anchor-offset mb-30" id="informazioni">
                <h2 class="mb-3"><?php echo Text::_('TPL_ACCESSIBILE_SERVICE_INFO'); ?></h2>
                <div class="richtext-wrapper lora">
                    <?php echo $seriesContent; ?>
                    <?php echo $scheduleContent; ?>
                    <?php echo $ticketsContent; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // Data ultimo aggiornamento ?>
            <?php if ($params->get('event_show_modify_date', 1) && !empty($event->modified)) : ?>
                <div class="mt-5 pt-4 border-top">
                    <p class="text-paragraph-small text-muted">
                        <?php echo Text::_('TPL_ACCESSIBILE_SERVICE_UPDATED'); ?>
                        <?php echo HTMLHelper::_('date', $event->modified, Text::_('DATE_FORMAT_LC3')); ?>
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
