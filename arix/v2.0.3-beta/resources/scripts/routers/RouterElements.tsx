import React from 'react';
import { ServerContext } from '@/state/server';
import routes from '@/routers/routes';
import Can from '@/components/elements/Can';
import { ChevronDownIcon } from '@heroicons/react/outline';
import { NavLink, Route, Switch, useRouteMatch } from 'react-router-dom';
import PermissionRoute from '@/components/elements/PermissionRoute';
import Spinner from '@/components/elements/Spinner';
import { NotFound } from '@/components/elements/ScreenBlock';
import TransitionRouter from '@/TransitionRouter';
import { useLocation } from 'react-router';
import { useTranslation } from 'react-i18next';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

interface Props {
    route: any;
}

// Helper function to check if route should be displayed based on nest/egg IDs
const shouldDisplayRoute = (route: any, nestId?: number, eggId?: number): boolean => {
    return (
        (route.nestIds && route.nestIds.includes(nestId ?? 0)) ||
        (route.eggIds && route.eggIds.includes(eggId ?? 0)) ||
        (route.nestId && route.nestId === nestId) ||
        (route.eggId && route.eggId === eggId) ||
        (!route.eggIds && !route.nestIds && !route.nestId && !route.eggId)
    );
};

// Helper function to render icon based on icon type
const renderIcon = (route: any, iconType: string) => {
    if (!route.icon) return null;

    const iconMap: Record<string, number> = {
        heroicons: 0,
        heroiconsFilled: 1,
        lucide: 2,
        remixicon: 3,
        remixiconFilled: 4,
    };

    const iconIndex = iconMap[iconType];
    return iconIndex !== undefined && route.icon[iconIndex]
        ? React.createElement(route.icon[iconIndex], { size: '1.25rem' })
        : null;
};

const NavItem = ({ route }: Props) => {
    const { t } = useTranslation('arix/navigation');
    const match = useRouteMatch<{ id: string }>();

    const icon = useStoreState((state: ApplicationStore) => state.settings.data!.arix.icon);
    const nestId = ServerContext.useStoreState((state) => state.server.data?.nestId);
    const eggId = ServerContext.useStoreState((state) => state.server.data?.eggId);

    const to = (value: string, url = false) => {
        return `${(url ? match.url : match.path).replace(/\/*$/, '')}/${value.replace(/^\/+/, '')}`;
    };

    if (!shouldDisplayRoute(route, nestId, eggId)) return null;

    const handleContextMenu = (event: React.MouseEvent) => {
        event.preventDefault();
        window.open(`${to(route.path, true)}?floating=true`, '_blank', 'height=500,width=800,left=100,top=100');
    };

    return (
        <NavLink to={to(route.path, true)} exact={route.exact} onContextMenu={handleContextMenu}>
            {renderIcon(route, icon)}
            <span>{t(route.name)}</span>
        </NavLink>
    );
};

const renderNavItem = (route: any) => {
    const consolePage = useStoreState((state: ApplicationStore) => state.settings.data!.arix.consolePage);
    
    if (route.path === '/console' && String(consolePage) === 'false') {
        return null;
    }

    return (
        route.permission ? (
            <Can key={route.path} action={route.permission} matchAny>
                <NavItem route={route} />
            </Can>
        ) : (
            <React.Fragment key={route.path}>
                <NavItem route={route} />
            </React.Fragment>
        )
    )
};

// Reusable dropdown component
const NavigationDropdown = ({ label, routes }: { label: string; routes: any[] }) => {
    return (
        <div className="dropdown">
            <span>{label} <ChevronDownIcon className="w-3"/></span>
            <div className="dropdown-body">
                {routes.filter((route) => !!route.name).map(renderNavItem)}
            </div>
        </div>
    );
};

export const SubNavigationLinks = () => {
    const { t } = useTranslation('arix/navigation');
    
    return (
        <>
        {routes.server.general
            .filter((route) => !!route.name)
            .map(renderNavItem)}
        <NavigationDropdown label={t('management')} routes={routes.server.management} />
        <NavigationDropdown label={t('configuration')} routes={routes.server.configuration} />
        </>
    );
};

// Reusable navigation section component
const NavigationSection = ({ label, routes }: { label: string; routes: any[] }) => {
    return (
        <div>
            <span>{label}</span>
            {routes.filter((route) => !!route.name).map(renderNavItem)}
        </div>
    );
};

export const Navigation = () => {
  const { t } = useTranslation('arix/navigation');
  
  return (
    <>
      <NavigationSection label={t('general')} routes={routes.server.general} />
      <NavigationSection label={t('management')} routes={routes.server.management} />
      <NavigationSection label={t('configuration')} routes={routes.server.configuration} />
    </>
  );
};

// Helper to render route components
const renderRouteComponent = (
    route: any,
    serverNestId: number | undefined,
    serverEggId: number | undefined,
    toPath: (value: string) => string
) => {
    const { path, permission, component: Component, nestIds, eggIds, nestId, eggId } = route;

    if (!shouldDisplayRoute(route, serverNestId, serverEggId)) return null;

    return (
        <PermissionRoute key={path} permission={permission} path={toPath(path)} exact>
            <Spinner.Suspense>
                <Component />
            </Spinner.Suspense>
        </PermissionRoute>
    );
};

export const ComponentLoader = () => {
  const match = useRouteMatch<{ id: string }>();
  const location = useLocation();

  const serverNestId = ServerContext.useStoreState((state) => state.server.data?.nestId);
  const serverEggId = ServerContext.useStoreState((state) => state.server.data?.eggId);

  const to = (value: string, url = false) => {
    return `${(url ? match.url : match.path).replace(/\/*$/, '')}/${value.replace(/^\/+/, '')}`;
  };

  const allRoutes = [
      ...routes.server.general,
      ...routes.server.management,
      ...routes.server.configuration,
  ];

  return (
    <TransitionRouter>
      <Switch location={location}>
        {allRoutes.map((route) => renderRouteComponent(route, serverNestId, serverEggId, to))}
        <Route path={'*'} component={NotFound} />
      </Switch>
    </TransitionRouter>
  );
};
