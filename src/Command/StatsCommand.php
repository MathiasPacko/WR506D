<?php

namespace App\Command;

use App\Repository\ActorRepository;
use App\Repository\CategoryRepository;
use App\Repository\MediaObjectRepository;
use App\Repository\MovieRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:stats',
    description: 'Affiche les statistiques de l\'application (films, acteurs, catégories, images)',
)]
class StatsCommand extends Command
{
    public function __construct(
        private MovieRepository $movieRepository,
        private ActorRepository $actorRepository,
        private CategoryRepository $categoryRepository,
        private MediaObjectRepository $mediaObjectRepository,
        private ParameterBagInterface $params,
        private MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Type de statistiques (all, movies, actors, categories, images)')
            ->addArgument('format', InputArgument::OPTIONAL, 'Format de sortie (text, json)', 'text')
            ->addOption('log-file', 'l', InputOption::VALUE_REQUIRED, 'Chemin du fichier de log où écrire les résultats')
            ->addOption('email', 'e', InputOption::VALUE_REQUIRED, 'Adresse email pour envoyer les statistiques');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer les arguments et options
        $type = $input->getArgument('type');
        $format = $input->getArgument('format');
        $logFile = $input->getOption('log-file');
        $emailRecipient = $input->getOption('email');

        // Valider le type
        $validTypes = ['all', 'movies', 'actors', 'categories', 'images'];
        if (!in_array($type, $validTypes)) {
            $io->error("Type invalide. Types valides : " . implode(', ', $validTypes));
            return Command::FAILURE;
        }

        // Collecter les statistiques
        $stats = $this->collectStats($type);

        // Formater et afficher les statistiques
        $content = $this->formatStats($stats, $format, $io);

        // Écrire dans un fichier si demandé
        if ($logFile) {
            $this->writeToFile($logFile, $content, $io);
        }

        // Envoyer par email si demandé (bonus)
        if ($emailRecipient) {
            $this->sendByEmail($emailRecipient, $content, $type, $io);
        }

        $io->success('Statistiques traitées avec succès !');

        return Command::SUCCESS;
    }

    private function collectStats(string $type): array
    {
        $stats = [];

        if ($type === 'all' || $type === 'movies') {
            $stats['movies'] = $this->movieRepository->count([]);
        }

        if ($type === 'all' || $type === 'actors') {
            $stats['actors'] = $this->actorRepository->count([]);
        }

        if ($type === 'all' || $type === 'categories') {
            $categories = $this->categoryRepository->findAll();
            $stats['categories'] = [
                'total' => count($categories),
                'details' => []
            ];

            foreach ($categories as $category) {
                $stats['categories']['details'][] = [
                    'name' => $category->getName(),
                    'movies_count' => $category->getMovies()->count()
                ];
            }
        }

        if ($type === 'all' || $type === 'images') {
            $nbMediaObjects = $this->mediaObjectRepository->count([]);

            // Calculer l'espace disque occupé
            $mediaPath = $this->params->get('kernel.project_dir') . '/public/media';
            $totalSize = 0;

            if (is_dir($mediaPath)) {
                $mediaObjects = $this->mediaObjectRepository->findAll();
                foreach ($mediaObjects as $mediaObject) {
                    if ($mediaObject->filePath) {
                        $filePath = $mediaPath . '/' . $mediaObject->filePath;
                        if (file_exists($filePath)) {
                            $totalSize += filesize($filePath);
                        }
                    }
                }
            }

            $stats['images'] = [
                'total' => $nbMediaObjects,
                'disk_space_mb' => round($totalSize / (1024 * 1024), 2)
            ];
        }

        return $stats;
    }

    private function formatStats(array $stats, string $format, SymfonyStyle $io): string
    {
        if ($format === 'json') {
            $content = json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $io->writeln($content);
            return $content;
        }

        // Format texte par défaut
        $content = "📊 Statistiques de l'application Rellflix\n";
        $content .= "==========================================\n";
        $content .= "Date : " . date('Y-m-d H:i:s') . "\n\n";

        $io->title('📊 Statistiques de l\'application Rellflix');

        if (isset($stats['movies'])) {
            $io->section('🎬 Films');
            $io->text("Nombre total de films : <info>{$stats['movies']}</info>");
            $content .= "🎬 Films\n";
            $content .= "Nombre total de films : {$stats['movies']}\n\n";
        }

        if (isset($stats['actors'])) {
            $io->section('👤 Acteurs');
            $io->text("Nombre total d'acteurs : <info>{$stats['actors']}</info>");
            $content .= "👤 Acteurs\n";
            $content .= "Nombre total d'acteurs : {$stats['actors']}\n\n";
        }

        if (isset($stats['categories'])) {
            $io->section('📁 Catégories');
            $io->text("Nombre total de catégories : <info>{$stats['categories']['total']}</info>");
            $content .= "📁 Catégories\n";
            $content .= "Nombre total de catégories : {$stats['categories']['total']}\n";

            if (!empty($stats['categories']['details'])) {
                $io->newLine();
                $io->text('Détail par catégorie :');
                $content .= "Détail par catégorie :\n";

                foreach ($stats['categories']['details'] as $cat) {
                    $io->text("  - {$cat['name']} : <comment>{$cat['movies_count']} film(s)</comment>");
                    $content .= "  - {$cat['name']} : {$cat['movies_count']} film(s)\n";
                }
            }
            $content .= "\n";
        }

        if (isset($stats['images'])) {
            $io->section('🖼️  Images');
            $io->text("Nombre total d'images : <info>{$stats['images']['total']}</info>");
            $io->text("Espace disque occupé : <info>{$stats['images']['disk_space_mb']} Mo</info>");
            $content .= "🖼️  Images\n";
            $content .= "Nombre total d'images : {$stats['images']['total']}\n";
            $content .= "Espace disque occupé : {$stats['images']['disk_space_mb']} Mo\n\n";
        }

        return $content;
    }

    private function writeToFile(string $logFile, string $content, SymfonyStyle $io): void
    {
        try {
            file_put_contents($logFile, $content);
            $io->info("Statistiques écrites dans le fichier : {$logFile}");
        } catch (\Exception $e) {
            $io->error("Erreur lors de l'écriture du fichier : {$e->getMessage()}");
        }
    }

    private function sendByEmail(string $recipient, string $content, string $type, SymfonyStyle $io): void
    {
        try {
            $email = (new Email())
                ->from('noreply@rellflix.com')
                ->to($recipient)
                ->subject("Statistiques Rellflix - {$type} - " . date('Y-m-d H:i:s'))
                ->text($content);

            $this->mailer->send($email);
            $io->info("Statistiques envoyées par email à : {$recipient}");
        } catch (\Exception $e) {
            $io->warning("Impossible d'envoyer l'email : {$e->getMessage()}");
        }
    }
}
