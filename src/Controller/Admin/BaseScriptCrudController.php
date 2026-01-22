<?php

namespace App\Controller\Admin;

use App\Entity\BaseScript;
use Doctrine\ORM\Query\Expr\Base;
use App\Controller\BaseScriptController;
use Dom\Text;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class BaseScriptCrudController extends AbstractCrudController
{
    private UrlGeneratorInterface $router;
    private string $scriptsDirectory;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(UrlGeneratorInterface $router, string $scriptsDirectory, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->router = $router;
        $this->scriptsDirectory = $scriptsDirectory;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }


    public static function getEntityFqcn(): string
    {
        return BaseScript::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Base de scripts')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }



    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),
            ChoiceField::new('language', 'Langage')
                ->setChoices([
                    'Python' => 'python',
                    'R' => 'r',
                    'Julia' => 'julia',
                ]),
            TextareaField::new('description'),

            // Champ VichUploader pour upload
            Field::new('file', 'Fichier')
                ->setFormType(FileType::class)
                ->onlyOnForms(),

            // Bouton téléchargement sur l'index
            TextField::new('filename', 'Cliquer pour télécharger')
                ->formatValue(
                    fn($value, $entity) =>
                    $value
                        ? sprintf(
                            '<a class="btn btn-sm btn-primary" href="%s"> %s</a>',
                            $this->router->generate(
                                'script_download',
                                ['id' => $entity->getId()]
                            ),
                            $value
                        )
                        : ''
                )
                ->renderAsHtml()
                ->onlyOnIndex(),


            // Contenu du fichier sur la page detail
            Field::new('_script_content', 'Contenu du script')
                ->setVirtual(true)
                ->formatValue(function ($value, $entity) {
                    $filename = $entity->getFilename();

                    if (!$filename) {
                        return null; // Aucun fichier
                    }

                    $path = '/var/www/html/ligair.local/public/uploads/scripts/' . $filename;

                    if (!file_exists($path) || !is_readable($path)) {
                        return null; // Contenu inaccessible
                    }

                    // On lit simplement le contenu brut, sans htmlspecialchars
                    return file_get_contents($path);
                })
                ->setTemplatePath('admin/fields/script_content.html.twig')
                ->onlyOnDetail(),
            TextField::new('filename', '')
                ->formatValue(
                    fn($value, $entity) =>
                    $value
                        ? sprintf(
                            '<a class="btn btn-sm btn-primary" href="%s">Telecharger</a>',
                            $this->router->generate(
                                'script_download',
                                ['id' => $entity->getId()]
                            ),
                        )
                        : ''
                )
                ->renderAsHtml()
                ->onlyOnDetail(),

        ];
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ->add(Crud::PAGE_INDEX, Action::DELETE) // par défaut
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // on enlève les autres pour éviter le menu ...
        ;
    }
}
