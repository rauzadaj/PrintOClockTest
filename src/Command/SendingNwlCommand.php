<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class SendingNwlCommand extends Command
{
    protected static $defaultName = 'sending-nwl';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(EntityManagerInterface $entityManager, Environment $twig, \Swift_Mailer $mailer)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $conn = $this->entityManager->getConnection() ;
            $sql = '
                SELECT * FROM users AS u
                    INNER JOIN users_categories AS uc 
                        ON uc.users_id = u.id 
                WHERE uc.categories_id = :id 
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $arg1]);
            $tabUsers = $stmt->fetchAllAssociative();
            foreach ($tabUsers as $user) {

                $message = (new \Swift_Message('Newsletter'))
                    ->setFrom('send@example.com')
                    ->setTo($user['email'])
                    ->setBody(
                        $this->twig->render(
                            'email/emailingA.html.twig',
                            [
                                'firstname'=> $user['firstname'],
                                'newsCat'=> $user['categories_id']
                            ]
                        ),
                        'text/html'
                    );
                $this->mailer->send($message);
            }
        }

        if ($input->getOption('option1')) {
            // ...
        }

//        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
