<?php

namespace App\Controller\Admin;

use Dom\Text;
use App\Entity\BaseScript;
use Doctrine\ORM\Query\Expr\Base;
use App\Controller\BaseScriptController;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use FOS\CKEditorBundle\Renderer\CKEditorRenderer;
use Symfony\Component\Validator\Constraints\Date;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('language', 'Langage'));
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
            DateField::new('createdAt', 'Date d\'ajout')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->hideOnForm(),
            ChoiceField::new('language', 'Langage')
                ->setChoices([
                    'Python' => 'python',
                    'R' => 'r',
                    'Julia' => 'julia',
                ]),
            AssociationField::new('addedBy', 'Pilote'),
            TextareaField::new('description')
                ->setFormType(CKEditorType::class)
                ->renderAsHtml(),

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
