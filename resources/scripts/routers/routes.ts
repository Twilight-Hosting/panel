import React, { lazy } from 'react';
import ServerConsole from '@/components/server/console/ServerConsoleContainer';
import DashboardContainer from '@/components/server/dashboard/DashboardContainer';
import FullConsoleContainer from '@/components/server/console/FullConsoleContainer';
import DatabasesContainer from '@/components/server/databases/DatabasesContainer';
import ScheduleContainer from '@/components/server/schedules/ScheduleContainer';
import UsersContainer from '@/components/server/users/UsersContainer';
import BackupContainer from '@/components/server/backups/BackupContainer';
import NetworkContainer from '@/components/server/network/NetworkContainer';
import StartupContainer from '@/components/server/startup/StartupContainer';
import FileManagerContainer from '@/components/server/files/FileManagerContainer';
import ModpacksContainer from '@/components/server/minecraft-modpacks/ModpacksContainer';
import SettingsContainer from '@/components/server/settings/SettingsContainer';
import AccountOverviewContainer from '@/components/dashboard/AccountOverviewContainer';
import ActivityLogContainer from '@/components/dashboard/activity/ActivityLogContainer';
import ServerActivityLogContainer from '@/components/server/ServerActivityLogContainer';
import PropertiesContainer from '@/components/server/properties/PropertiesContainer';
import DomainsContainer from '@/components/server/subdomains/SubdomainsContainer';
import PluginsContainer from '@/components/server/plugins/PluginsContainer';
import VersionsContainer from '@/components/server/versions/VersionsContainer';
import { UserIcon, EyeIcon, CollectionIcon, ViewGridIcon, TerminalIcon, FolderOpenIcon, DatabaseIcon, CalendarIcon, UserGroupIcon, ArchiveIcon, GlobeIcon, GlobeAltIcon, AdjustmentsIcon, DocumentTextIcon, CogIcon, DocumentDownloadIcon, ViewGridAddIcon } from '@heroicons/react/outline'
import PlayerContainer from '@/components/server/player/PlayerContainer';

const FileEditContainer = lazy(() => import('@/components/server/files/FileEditContainer'));
const ScheduleEditContainer = lazy(() => import('@/components/server/schedules/ScheduleEditContainer'));

/*
        ██╗██╗  ░██╗░░░░░░░██╗░█████╗░██████╗░███╗░░██╗  ██╗██╗
        ██║██║  ░██║░░██╗░░██║██╔══██╗██╔══██╗████╗░██║  ██║██║
        ██║██║  ░╚██╗████╗██╔╝███████║██████╔╝██╔██╗██║  ██║██║
        ╚═╝╚═╝  ░░████╔═████║░██╔══██║██╔══██╗██║╚████║  ╚═╝╚═╝
        ██╗██╗  ░░╚██╔╝░╚██╔╝░██║░░██║██║░░██║██║░╚███║  ██╗██╗
        ╚═╝╚═╝  ░░░╚═╝░░░╚═╝░░╚═╝░░╚═╝╚═╝░░╚═╝╚═╝░░╚══╝  ╚═╝╚═╝


        Read this before doing addon modifications

        Arix Theme has already handled many panel
        modifications for you, so there's no need for
        any changes in the "ServerRouter.tsx" file.

        To add an adodn to your theme, you just need
        to add an icon from the Heroicons font pack to
        the import statement on line 16. You can find
        the icons at https://v1.heroicons.com/. For
        instance, if you want to add "inbox-in", include
        the following in the import statement:

        "InboxInIcon,"

        Your import statement might look like this example:

        import { InboxInIcon, UserIcon, EyeIcon, ...

        After importing the desired icon, refer to the addon's
        readme file and include the required import line.
        An example might be:

        import PluginInstallerContainer from '@/components/server/plugin/PluginInstallerContainer';

        Once you've imported the correct icon and the component,
        you simply need to follow the instructions in the addon's
        readme to add the route. Don't forget to include
        the icon in the route definition. Here's an example:

        {
            path: '/plugin-installer',
            permission: null,
            name: 'Plugin installer',
            icon: InboxInIcon,
            component: PluginInstallerContainer,
            exact: true,
        },
*/

interface RouteDefinition {
    path: string;
    // If undefined is passed this route is still rendered into the router itself
    // but no navigation link is displayed in the sub-navigation menu.
    name: string | undefined;
    component: React.ComponentType;
    icon?: React.ComponentType;
    exact?: boolean;
}

interface ServerRouteDefinition extends RouteDefinition {
    permission: string | string[] | null;
    nestId?: number;
    eggId?: number;
    nestIds?: number[];
    eggIds?: number[];
}

interface Routes {
    // All of the routes available under "/account"
    account: RouteDefinition[];
    // All of the routes available under "/server/:id"
    server: {
        general: ServerRouteDefinition[];
        management: ServerRouteDefinition[];
        configuration: ServerRouteDefinition[];
    };
}

export default {
    account: [
        {
            path: '/',
            name: 'account',
            icon: UserIcon,
            component: AccountOverviewContainer,
            exact: true,
        },
        {
            path: '/activity',
            name: 'account-activity',
            icon: EyeIcon,
            component: ActivityLogContainer,
        },
    ],
    server: {
        general: [
            {
                path: '/console',
                permission: null,
                name: 'console',
                icon: TerminalIcon,
                component: ServerConsole,
                exact: true,
            },
            {
                path: '/console/popup',
                permission: null,
                icon: TerminalIcon,
                name: undefined,
                component: FullConsoleContainer,
            },
            {
                path: '/settings',
                permission: ['settings.*', 'file.sftp'],
                name: 'settings',
                icon: CogIcon,
                component: SettingsContainer,
            },
            {
                path: '/activity',
                permission: 'activity.*',
                name: 'activity',
                icon: EyeIcon,
                component: ServerActivityLogContainer,
            },
            {
                path: '/',
                permission: null,
                name: 'dashboard',
                icon: ViewGridIcon,
                component: DashboardContainer,
                exact: true,
            },
        ],
        management: [
            {
                path: '/files',
                permission: 'file.*',
                name: 'files',
                icon: FolderOpenIcon,
                component: FileManagerContainer,
            },
            {
                path: '/files/:action(edit|new)',
                permission: 'file.*',
                name: undefined,
                component: FileEditContainer,
            },
            {
                path: '/databases',
                permission: 'database.*',
                name: 'databases',
                icon: DatabaseIcon,
                component: DatabasesContainer,
            },
            {
                path: '/backups',
                permission: 'backup.*',
                name: 'backups',
                icon: ArchiveIcon,
                component: BackupContainer,
            },
            {
                path: '/network',
                permission: 'allocation.*',
                name: 'network',
                icon: GlobeIcon,
                component: NetworkContainer,
            },
            {
                path: '/player-manager',
                permission: 'file.*',
                name: 'Player Manager',
                component: PlayerContainer,
                eggIds: [16],
                icon: UserIcon,
            },
            {
                path: '/plugins',
                permission: 'file.*',
                name: 'plugins',
                nestId: 1,
                icon: DocumentDownloadIcon,
                component: PluginsContainer,
            },
            {
                path: '/modpacks',
                permission: 'file.*',
                name: 'Modpacks',
                component: ModpacksContainer,
                eggIds: [1, 3, 15],
                icon: ViewGridAddIcon,
            },
            {
                path: '/domains',
                permission: 'file.*',
                name: 'subdomains',
                icon: GlobeAltIcon,
                component: DomainsContainer,
            },
        ],
        configuration: [
            {
                path: '/schedules',
                permission: 'schedule.*',
                name: 'schedules',
                icon: CalendarIcon,
                component: ScheduleContainer,
            },
            {
                path: '/schedules/:id',
                permission: 'schedule.*',
                name: undefined,
                component: ScheduleEditContainer,
            },
            {
                path: '/users',
                permission: 'user.*',
                name: 'users',
                icon: UserGroupIcon,
                component: UsersContainer,
            },
            {
                path: '/startup',
                permission: 'startup.*',
                name: 'startup',
                icon: AdjustmentsIcon,
                component: StartupContainer,
            },
            {
                path: '/versions',
                permission: 'versions.*',
                name: 'versions',
                nestId: 1,
                icon: CollectionIcon,
                component: VersionsContainer,
            },
            {
                path: '/properties',
                permission: 'file.update',
                name: 'properties',
                nestId: 1,
                icon: DocumentTextIcon,
                component: PropertiesContainer,
            },
        ],
    },
} as Routes;
