<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ranked — Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkTo(EleveCrudController::class, 'Élèves', 'fa fa-user-graduate');
        yield MenuItem::linkTo(ProfesseurCrudController::class, 'Professeurs', 'fa fa-chalkboard-teacher');

        yield MenuItem::section('Contenu pédagogique');
        yield MenuItem::linkTo(MatiereCrudController::class, 'Matières', 'fa fa-book');
        yield MenuItem::linkTo(DifficulteCrudController::class, 'Difficultés', 'fa fa-layer-group');
        yield MenuItem::linkTo(CoursCrudController::class, 'Cours', 'fa fa-graduation-cap');
        yield MenuItem::linkTo(CompetenceCrudController::class, 'Compétences', 'fa fa-star');

        yield MenuItem::section('Classes');
        yield MenuItem::linkTo(ClasseCrudController::class, 'Classes', 'fa fa-users');

        yield MenuItem::section('Activités');
        yield MenuItem::linkTo(ActiviteCrudController::class, 'Activités', 'fa fa-tasks');
        yield MenuItem::linkTo(ContenuCrudController::class, 'Contenus', 'fa fa-file');
        yield MenuItem::linkTo(QcmCrudController::class, 'QCMs', 'fa fa-question-circle');
        yield MenuItem::linkTo(QuestionCrudController::class, 'Questions', 'fa fa-question');
        yield MenuItem::linkTo(ReponseCrudController::class, 'Réponses', 'fa fa-check');

        yield MenuItem::section('Progression');
        yield MenuItem::linkTo(BadgeCrudController::class, 'Badges', 'fa fa-trophy');
        yield MenuItem::linkTo(ProgressionCrudController::class, 'Progressions', 'fa fa-chart-line');
        yield MenuItem::linkTo(ActiviteProgressionCrudController::class, 'Progression activités', 'fa fa-history');
        yield MenuItem::linkTo(EleveCompetenceCrudController::class, 'Compétences élèves', 'fa fa-certificate');

        yield MenuItem::section('Sécurité');
        yield MenuItem::linkTo(PasswordResetTokenCrudController::class, 'Tokens reset MDP', 'fa fa-key');

        yield MenuItem::linkToLogout('Se déconnecter', 'fa fa-sign-out');
    }
}
