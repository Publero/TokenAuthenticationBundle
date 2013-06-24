<?php
namespace Publero\TokenAuthenticationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class GenerateAccessTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('publero:access-token:generate')
            ->setDescription('Generate access token')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('roles', InputArgument::IS_ARRAY, 'Roles to apply to the access token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userProvider = $this->getContainer()->get('publero_token_authentication.user_provider');
        $user = $userProvider->loadUserByUsername($input->getArgument('username'));

        $dialog = $this->getHelperSet()->get('dialog');
        $roles = $input->getArgument('roles');
        if (empty($roles)) {
            if ($dialog->askConfirmation($output, '<question>Configure access token roles?</question> ', false)) {
                $roles = array();
                foreach ($this->getReachableRoles($user) as $role) {
                    if ($dialog->askConfirmation($output, sprintf('<question>Assign role: %s?</question> ', $role), false)) {
                        $roles[] = $role;
                    }
                }
            } else {
                $roles = null;
            }
        }

        $accessTokenManager = $this->getContainer()->get('publero_token_authentication.access_token_manager');
        $accessToken = $accessTokenManager->generateAccessToken($user, $roles);
        $accessTokenManager->getManager()->persist($accessToken);
        $accessTokenManager->getManager()->flush($accessToken);

        $output->writeln(sprintf('Generated access token: %s', $accessToken->getToken()));
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    private function getReachableRoles(UserInterface $user)
    {
        $roleHierarchy = $this->getContainer()->get('security.role_hierarchy');
        $roles = $roleHierarchy->getReachableRoles(array_map(
            function($role) {
                return new Role($role);
            },
            $user->getRoles()
        ));

        return array_map(
            function(Role $role) {
                return $role->getRole();
            },
            $roles
        );
    }
}
