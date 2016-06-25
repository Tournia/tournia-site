<?php
namespace TS\SiteBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav');

        $tournament = $options['tournament'];
        $tournamentUrl = $tournament->getUrl();

        foreach ($tournament->getSitePages() as $page) {
            $menu->addChild($page->getId(), array(
                'route' => 'website_page',
                'routeParameters' => array('tournamentUrl' => $tournamentUrl, 'page' => $page->getUrl()),
                'label' => $page->getTitle()
            ));
        }
        
        /*$menu->addChild('Page 21', array(
            'route' => 'website_page',
            'routeParameters' => array('tournamentUrl' => $tournamentUrl, 'page' => 'abc')
        ));
        $menu['Page 21']->addChild('Page 22', array(
            'route' => 'website_page',
            'routeParameters' => array('tournamentUrl' => $tournamentUrl, 'page' => 'abc')
        ));
        $menu->addChild('x1title2', array(
            'route' => 'website_page',
            'routeParameters' => array('tournamentUrl' => $tournamentUrl, 'page' => 'def')
        ));*/
        
        return $menu;
    }
}