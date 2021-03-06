<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use org\bovigo\vfs\vfsStream;
use Mockery as m;

class AdministrationControllerTest extends MockeryTestCase
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $configHandler;
    private $formFactory;
    private $analyticsManager;
    private $translator;
    private $request;
    private $mailManager;
    private $localeManager;
    private $router;

    protected function setUp()
    {
        parent::setUp();
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->groupManager = $this->mock('Claroline\CoreBundle\Manager\GroupManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->workspaceTagManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceTagManager');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->configHandler = $this->mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->analyticsManager = $this->mock('Claroline\CoreBundle\Manager\AnalyticsManager');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->mailManager = $this->mock('Claroline\CoreBundle\Manager\MailManager');
        $this->localeManager = $this->mock('Claroline\CoreBundle\Manager\LocaleManager');
        $this->router = $this->mock('Symfony\Component\Routing\RouterInterface');
    }

    public function testIndexAction()
    {
        $this->assertEquals(array(), $this->getController()->indexAction());
    }

    public function testUserCreationFormAction()
    {
        $user = new User();
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->roleManager->shouldReceive('getPlatformRoles')
            ->with($user)
            ->once()
            ->andReturn($roles);
        $this->localeManager->shouldReceive('getAvailableLocales')
            ->once()
            ->andReturn(array('en'));
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_FULL, array($roles, array('en')))
            ->once()
            ->andReturn($form);
        $this->mailManager
            ->shouldReceive('isMailerAvailable')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form_complete_user' => 'view'),
            $this->getController()->userCreationFormAction($user)
        );
    }

    public function testCreateUserAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $currentUser = new User();
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $roleA = new Role();
        $roleB = new Role();
        $roleC = new Role();
        $roleD = new Role();
        $roles = array($roleA, $roleB);
        $newRoles = new ArrayCollection(array($roleC, $roleD));
        $form = $this->mock('Symfony\Component\Form\Form');
        $formInterface = $this->mock('Symfony\Component\Form\FormInterface');

        $this->roleManager->shouldReceive('getPlatformRoles')
            ->with($currentUser)
            ->once()
            ->andReturn($roles);
        $this->localeManager->shouldReceive('getAvailableLocales')
            ->once()
            ->andReturn(array('en'));
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_FULL, array($roles, array('en')))
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($user);
        $form->shouldReceive('get')
            ->with('platformRoles')
            ->once()
            ->andReturn($formInterface);
        $formInterface->shouldReceive('getData')
            ->once()
            ->andReturn($newRoles);
        $this->userManager->shouldReceive('insertUserWithRoles')
            ->with($user, $newRoles)
            ->once();
        $this->mailManager
            ->shouldReceive('isMailerAvailable')
            ->once()
            ->andReturn(true);
        $this->translator
            ->shouldReceive('trans')
            ->with('username', array(), 'platform')
            ->once()
            ->andReturn('admin form username');
        $user->shouldReceive('getUsername')
            ->once()
            ->andReturn('username');
        $this->translator
            ->shouldReceive('trans')
            ->with('password', array(), 'platform')
            ->once()
            ->andReturn('admin form plainPassword first');
        $user->shouldReceive('getPlainPassword')
            ->once()
            ->andReturn('password');
        $user->shouldReceive('getMail')
            ->once()
            ->andReturn('mail');
        $this->mailManager
            ->shouldReceive('sendCreationMessage')
            ->with(
                'noreply@claroline.net',
                'mail',
                'admin form username: usernameadmin form plainPassword first: password'
            )
            ->once()
            ->andReturn(true);
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_user_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->createUserAction($currentUser)
        );
    }

    public function testDeleteUsersAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);

        $this->userManager->shouldReceive('deleteUser')
            ->with($userA)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserDelete', array($userA))
            ->once();
        $this->userManager->shouldReceive('deleteUser')
            ->with($userB)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogUserDelete', array($userB))
            ->once();

        $this->getController()->deleteUsersAction($users);
    }

    public function testUserListActionWithoutSearch()
    {
        $this->userManager->shouldReceive('getAllUsers')
            ->with(1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'max' => 50, 'order' => 'id'),
            $this->getController()->userListAction(1, '', 50, 'id')
        );
    }

    public function testUserListActionWithSearch()
    {
        $this->userManager->shouldReceive('getUsersByName')
            ->with('search', 1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'max' => 50, 'order' => 'id'),
            $this->getController()->userListAction(1, 'search', 50, 'id')
        );
    }

    public function testGroupListActionWithoutSearch()
    {
        $this->groupManager->shouldReceive('getGroups')
            ->with(1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'max' => 50, 'order' => 'id'),
            $this->getController()->groupListAction(1, '', 50, 'id')
        );
    }

    public function testGroupListActionWithSearch()
    {
        $this->groupManager->shouldReceive('getGroupsByName')
            ->with('search', 1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'max' => 50, 'order' => 'id'),
            $this->getController()->groupListAction(1, 'search', 50, 'id')
        );
    }

    public function testUsersOfGroupListActionWithoutSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getUsersByGroup')
            ->with($group, 1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'group' => $group, 'max' => 50, 'order' => 'id'),
            $this->getController()->usersOfGroupListAction($group, 1, '', 50, 'id')
        );
    }

    public function testUsersOfGroupListActionWithSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getUsersByNameAndGroup')
            ->with('search', $group, 1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'group' => $group, 'max' => 50, 'order' => 'id'),
            $this->getController()->usersOfGroupListAction($group, 1, 'search', 50, 'id')
        );
    }

    public function testOutsideOfGroupUserListActionWithoutSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getGroupOutsiders')
            ->with($group, 1, 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => '', 'group' => $group, 'max' => 50, 'order' => 'id'),
            $this->getController()->outsideOfGroupUserListAction($group, 1, '', 50, 'id')
        );
    }

    public function testOutsideOfGroupUserListActionWithSearch()
    {
        $group = new Group();

        $this->userManager->shouldReceive('getGroupOutsidersByName')
            ->with($group, 1, 'search', 50, 'id')
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('pager' => 'pager', 'search' => 'search', 'group' => $group, 'max' => 50, 'order' => 'id'),
            $this->getController()->outsideOfGroupUserListAction($group, 1, 'search', 50, 'id')
        );
    }

    public function testGroupCreationFormAction()
    {
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form_group' => 'view'),
            $this->getController()->groupCreationFormAction()
        );
    }

    public function testCreateGroupAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $form = $this->mock('Symfony\Component\Form\Form');
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $role = new Role();

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP, array())
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($group);
        $this->roleManager->shouldReceive('getRoleByName')
            ->with('ROLE_USER')
            ->once()
            ->andReturn($role);
        $group->shouldReceive('setPlatformRole')
            ->with($role)
            ->once();
        $this->groupManager->shouldReceive('insertGroup')
            ->with($group)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupCreate', array($group))
            ->once();
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_group_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals('redirection', $controller->createGroupAction());
    }

    public function testAddUsersToGroupAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);
        $group = new Group();

        $this->groupManager->shouldReceive('addUsersToGroup')
            ->with($group, $users)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', array($group, $userA))
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupAddUser', array($group, $userB))
            ->once();

        $this->getController()->addUsersToGroupAction($group, $users);
    }

    public function testDeleteUsersFromGroupAction()
    {
        $userA = new User();
        $userB = new User();
        $users = array($userA, $userB);
        $group = new Group();

        $this->groupManager->shouldReceive('removeUsersFromGroup')
            ->with($group, $users)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupRemoveUser', array($group, $userA))
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupRemoveUser', array($group, $userB))
            ->once();

        $this->getController()->deleteUsersFromGroupAction($group, $users);
    }

    public function testDeleteGroupsAction()
    {
        $groupA = new Group();
        $groupB = new Group();
        $groups = array($groupA, $groupB);

        $this->groupManager->shouldReceive('deleteGroup')
            ->with($groupA)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupDelete', array($groupA))
            ->once();
        $this->groupManager->shouldReceive('deleteGroup')
            ->with($groupB)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('log', 'Log\LogGroupDelete', array($groupB))
            ->once();

        $this->getController()->deleteGroupsAction($groups);
    }

    public function testGroupSettingsFormAction()
    {
        $group = new Group();
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP_SETTINGS, array(), $group)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('form');

        $this->assertEquals(
            array('group' => $group, 'form_settings' => 'form'),
            $this->getController()->groupSettingsFormAction($group)
        );
    }

    public function testUpdateGroupSettingsAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $newGroup = $this->mock('Claroline\CoreBundle\Entity\Group');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $form = $this->mock('Symfony\Component\Form\Form');

        $group->shouldReceive('getPlatformRole')
            ->once()
            ->andReturn($role);
        $role->shouldReceive('getTranslationKey')
            ->once()
            ->andReturn('old_key');
        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_GROUP_SETTINGS, array(), $group)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $form->shouldReceive('getData')
            ->once()
            ->andReturn($newGroup);
        $this->groupManager->shouldReceive('updateGroup')
            ->with($newGroup, 'old_key')
            ->once();
        $controller->shouldReceive('generateUrl')
            ->with('claro_admin_group_list')
            ->once()
            ->andReturn('url');
        $controller->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->updateGroupSettingsAction($group)
        );
    }

    public function testPlatformSettingsFormAction()
    {
        $this->markTestSkipped('Unable to test because it uses getThemes which is a private function');
    }

    public function testUpdatePlatformSettingsAction()
    {
        $this->markTestSkipped('Unable to test because it uses getThemes which is a private function');
    }

    public function testPluginParametersAction()
    {
        $event = $this->mock('Claroline\CoreBundle\Event\PluginOptionsEvent');

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with('plugin_options_domain', 'PluginOptions', array())
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getResponse')
            ->once()
            ->andReturn('response');

        $this->assertEquals('response', $this->getController()->pluginParametersAction('domain'));
    }

    public function testUsersManagementAction()
    {
        $this->assertEquals(array(), $this->getController()->usersManagementAction());
    }

    public function testImportUsersFormAction()
    {
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(array('form' => 'view'), $this->getController()->importUsersFormAction());
    }

    public function testImportUsers()
    {
        vfsStream::setup('root', null, array('users.txt' => "gg,gg,gg,gg,gg,gg,gg"));

        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);

        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();

        $form->shouldReceive('isValid')
            ->with()
            ->once()
            ->andReturn(true);

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $form->shouldReceive('get->getData')->andReturn(vfsStream::url('root/users.txt'));
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        $this->userManager->shouldReceive('importUsers')->once()
            ->with(array(array('gg', 'gg', 'gg', 'gg', 'gg', 'gg', 'gg')));

        $this->router->shouldReceive('generate')->once()->with('claro_admin_user_list')->andReturn('yolo');

        $this->assertTrue($this->getController()->importUsers() instanceof RedirectResponse);
    }

    public function testImportUsersIntoGroupFormAction()
    {
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array('form' => 'view', 'group' => $group),
            $this->getController()->importUsersIntoGroupFormAction($group)
        );
    }

    public function testImportUsersIntoGroupAction()
    {
        vfsStream::setup('root', null, array('users.txt' => "gg,gg,gg,gg,gg,gg,gg"));
        $group = $this->mock('Claroline\CoreBundle\Entity\Group');
        $group->shouldReceive('getId')->andReturn(42);

        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory->shouldReceive('create')
            ->with(FormFactory::TYPE_USER_IMPORT)
            ->once()
            ->andReturn($form);

        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();

        $form->shouldReceive('isValid')
            ->with()
            ->once()
            ->andReturn(true);

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $form->shouldReceive('get->getData')->andReturn(vfsStream::url('root/users.txt'));
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        $users = array(array('gg', 'gg', 'gg', 'gg', 'gg', 'gg', 'gg'));
        $this->userManager->shouldReceive('importUsers')->once()->with($users);
        $this->groupManager->shouldReceive('importUsers')->once()->with($group, $users);
        $this->router->shouldReceive('generate')->once()
            ->with('claro_admin_user_of_group_list', array('groupId' => 42))
            ->andReturn('azertyuiop');

        $this->assertTrue($this->getController()->importUsersIntoGroupAction($group) instanceof RedirectResponse);
    }

    public function testRegistrationManagementActionWithoutSearch()
    {
        $datas = array(
            'workspaces' => 'workspaces',
            'tags' => 'tags',
            'tagWorkspaces' => 'tagWorkspaces',
            'hierarchy' => 'hierarchy',
            'rootTags' => 'rootTags',
            'displayable' => 'displayable'
        );

        $this->workspaceTagManager
            ->shouldReceive('getDatasForWorkspaceList')
            ->with(false)
            ->once()
            ->andReturn($datas);

        $this->assertEquals(
            array(
                'workspaces' => 'workspaces',
                'tags' => 'tags',
                'tagWorkspaces' => 'tagWorkspaces',
                'hierarchy' => 'hierarchy',
                'rootTags' => 'rootTags',
                'displayable' => 'displayable',
                'search' => ''
            ),
            $this->getController()->registrationManagementAction('')
        );
    }

    public function testRegistrationManagementActionWithSearch()
    {
        $this->workspaceManager
            ->shouldReceive('getDisplayableWorkspacesBySearchPager')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'workspaces' => 'pager',
                'search' => 'search'
            ),
            $this->getController()->registrationManagementAction('search')
        );
    }

    public function testRegistrationManagementUserListAction()
    {
        $workspaces = array('workspace_1', 'workspace_2');

        $this->userManager
            ->shouldReceive('getAllUsers')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'workspaces' => $workspaces,
                'users' => 'pager',
                'search' => ''
            ),
            $this->getController()->registrationManagementUserListAction($workspaces)
        );
    }

    public function testRegistrationManagementGroupListAction()
    {
        $workspaces = array('workspace_1', 'workspace_2');

        $this->groupManager
            ->shouldReceive('getGroups')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'workspaces' => $workspaces,
                'groups' => 'pager',
                'search' => ''
            ),
            $this->getController()->registrationManagementGroupListAction($workspaces)
        );
    }

    public function testUserListPagerActionWithSearch()
    {
        $this->userManager
            ->shouldReceive('getUsersByName')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'users' => 'pager',
                'search' => 'search'
            ),
            $this->getController()->userListPagerAction(1, 'search')
        );
    }

    public function testUserListPagerActionWithoutSearch()
    {
        $this->userManager
            ->shouldReceive('getAllUsers')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'users' => 'pager',
                'search' => ''
            ),
            $this->getController()->userListPagerAction(1, '')
        );
    }

    public function testGroupListPagerActionWithSearch()
    {
        $this->groupManager
            ->shouldReceive('getGroupsByName')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'groups' => 'pager',
                'search' => 'search'
            ),
            $this->getController()->groupListPagerAction(1, 'search')
        );
    }

    public function testGroupListPagerActionWithoutSearch()
    {
        $this->groupManager
            ->shouldReceive('getGroups')
            ->with(1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'groups' => 'pager',
                'search' => ''
            ),
            $this->getController()->groupListPagerAction(1, '')
        );
    }

    public function testSubscribeMultipleUsersToMultipleWorkspacesAction()
    {
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaces = array($workspaceA, $workspaceB);
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = array($userA, $userB);
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $msgA = 'firstA lastA has been suscribed with role "roleTrad"';
        $msgB = 'firstB lastB has been suscribed with role "roleTrad"';
        $msg = $msgA . '-;-' . $msgB . '-;-';

        $this->roleManager
            ->shouldReceive('getRoleByTranslationKeyAndWorkspace')
            ->with('role', $workspaceA)
            ->once()
            ->andReturn($roleA);
        $this->roleManager
            ->shouldReceive('associateRoleToMultipleSubjects')
            ->with($users, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('getRoleByTranslationKeyAndWorkspace')
            ->with('role', $workspaceB)
            ->once()
            ->andReturn($roleB);
        $this->roleManager
            ->shouldReceive('associateRoleToMultipleSubjects')
            ->with($users, $roleB)
            ->once();
        $userA->shouldReceive('getFirstName')
            ->once()
            ->andReturn('firstA');
        $userA->shouldReceive('getLastName')
            ->once()
            ->andReturn('lastA');
        $userB->shouldReceive('getFirstName')
            ->once()
            ->andReturn('firstB');
        $userB->shouldReceive('getLastName')
            ->once()
            ->andReturn('lastB');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'has_been_suscribed_with_role',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('has been suscribed with role');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'role',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleTrad');

        $response = $this->getController()
            ->subscribeMultipleUsersToMultipleWorkspacesAction(
                'role',
                $workspaces,
                $users
            );
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($msg, $response->getContent());
    }

    public function testSubscribeMultipleGroupsToMultipleWorkspacesAction()
    {
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaces = array($workspaceA, $workspaceB);
        $groupA = $this->mock('Claroline\CoreBundle\Entity\Group');
        $groupB = $this->mock('Claroline\CoreBundle\Entity\Group');
        $groups = array($groupA, $groupB);
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $msgA = '"groupA" has been suscribed with role "roleTrad"';
        $msgB = '"groupB" has been suscribed with role "roleTrad"';
        $msg = $msgA . '-;-' . $msgB . '-;-';

        $this->roleManager
            ->shouldReceive('getRoleByTranslationKeyAndWorkspace')
            ->with('role', $workspaceA)
            ->once()
            ->andReturn($roleA);
        $this->roleManager
            ->shouldReceive('associateRoleToMultipleSubjects')
            ->with($groups, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('getRoleByTranslationKeyAndWorkspace')
            ->with('role', $workspaceB)
            ->once()
            ->andReturn($roleB);
        $this->roleManager
            ->shouldReceive('associateRoleToMultipleSubjects')
            ->with($groups, $roleB)
            ->once();
        $groupA->shouldReceive('getName')
            ->once()
            ->andReturn('groupA');
        $groupB->shouldReceive('getName')
            ->once()
            ->andReturn('groupB');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'has_been_suscribed_with_role_group',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('has been suscribed with role');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'role',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleTrad');

        $response = new Response($msg, 200);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(
            $response->getContent(),
            $this->getController()
                ->subscribeMultipleGroupsToMultipleWorkspacesAction(
                    'role',
                    $workspaces,
                    $groups
                )->getContent()
        );
    }

    public function testSubscribeMultipleUsersToOneWorkspaceAction()
    {
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $userA = $this->mock('Claroline\CoreBundle\Entity\User');
        $userB = $this->mock('Claroline\CoreBundle\Entity\User');
        $users = array($userA, $userB);
        $msgAA = 'firstA lastA has been suscribed with role "roleATrad"';
        $msgAB = 'firstA lastA has been suscribed with role "roleBTrad"';
        $msgBA = 'firstB lastB has been suscribed with role "roleATrad"';
        $msgBB = 'firstB lastB has been suscribed with role "roleBTrad"';
        $msg = $msgAA . '-;-' . $msgAB . '-;-' . $msgBA . '-;-' . $msgBB . '-;-';

        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($userA, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($userA, $roleB)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($userB, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($userB, $roleB)
            ->once();

        $userA->shouldReceive('getFirstName')
            ->times(2)
            ->andReturn('firstA');
        $userA->shouldReceive('getLastName')
            ->times(2)
            ->andReturn('lastA');
        $userB->shouldReceive('getFirstName')
            ->times(2)
            ->andReturn('firstB');
        $userB->shouldReceive('getLastName')
            ->times(2)
            ->andReturn('lastB');
        $roleA->shouldReceive('getTranslationKey')
            ->times(2)
            ->andReturn('roleA');
        $roleB->shouldReceive('getTranslationKey')
            ->times(2)
            ->andReturn('roleB');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'has_been_suscribed_with_role',
                array(),
                'platform'
            )
            ->times(4)
            ->andReturn('has been suscribed with role');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'roleA',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleATrad');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'roleB',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleBTrad');

        $response = new Response($msg, 200);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(
            $response->getContent(),
            $this->getController()->subscribeMultipleUsersToOneWorkspaceAction(
                $roles,
                $users
            )->getContent()
        );
    }

    public function testSubscribeMultipleGroupsToOneWorkspaceAction()
    {
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $groupA = $this->mock('Claroline\CoreBundle\Entity\Group');
        $groupB = $this->mock('Claroline\CoreBundle\Entity\Group');
        $groups = array($groupA, $groupB);
        $msgAA = '"groupA" has been suscribed with role "roleATrad"';
        $msgAB = '"groupA" has been suscribed with role "roleBTrad"';
        $msgBA = '"groupB" has been suscribed with role "roleATrad"';
        $msgBB = '"groupB" has been suscribed with role "roleBTrad"';
        $msg = $msgAA . '-;-' . $msgAB . '-;-' . $msgBA . '-;-' . $msgBB . '-;-';

        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($groupA, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($groupA, $roleB)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($groupB, $roleA)
            ->once();
        $this->roleManager
            ->shouldReceive('associateRole')
            ->with($groupB, $roleB)
            ->once();

        $groupA->shouldReceive('getName')
            ->times(2)
            ->andReturn('groupA');
        $groupB->shouldReceive('getName')
            ->times(2)
            ->andReturn('groupB');
        $roleA->shouldReceive('getTranslationKey')
            ->times(2)
            ->andReturn('roleA');
        $roleB->shouldReceive('getTranslationKey')
            ->times(2)
            ->andReturn('roleB');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'has_been_suscribed_with_role_group',
                array(),
                'platform'
            )
            ->times(4)
            ->andReturn('has been suscribed with role');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'roleA',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleATrad');
        $this->translator
            ->shouldReceive('trans')
            ->with(
                'roleB',
                array(),
                'platform'
            )
            ->times(2)
            ->andReturn('roleBTrad');

        $response = new Response($msg, 200);
        $this->assertEquals(
            $response->getContent(),
            $this->getController()->subscribeMultipleGroupsToOneWorkspaceAction(
                $roles,
                $groups
            )->getContent()
        );
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $this->getController()->subscribeMultipleGroupsToOneWorkspaceAction(
                $roles,
                $groups
            )
        );
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new AdministrationController(
                $this->userManager,
                $this->roleManager,
                $this->groupManager,
                $this->workspaceManager,
                $this->workspaceTagManager,
                $this->security,
                $this->eventDispatcher,
                $this->configHandler,
                $this->formFactory,
                $this->analyticsManager,
                $this->translator,
                $this->request,
                $this->mailManager,
                $this->localeManager,
                $this->router
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Controller\AdministrationController' . $stringMocked,
            array(
                $this->userManager,
                $this->roleManager,
                $this->groupManager,
                $this->workspaceManager,
                $this->workspaceTagManager,
                $this->security,
                $this->eventDispatcher,
                $this->configHandler,
                $this->formFactory,
                $this->analyticsManager,
                $this->translator,
                $this->request,
                $this->mailManager,
                $this->localeManager,
                $this->router
            )
        );
    }
}
