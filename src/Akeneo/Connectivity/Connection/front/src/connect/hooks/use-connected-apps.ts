import {useEffect, useState} from 'react';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {ConnectedApp} from '../../model/Apps/connected-app';
import {useFeatureFlags} from '../../shared/feature-flags';
import {useFetchConnectedApps} from './use-fetch-connected-apps';
import {useFetchApps} from './use-fetch-apps';
import {useTranslate} from '../../shared/translate';
import {useFetchTestApps} from './use-fetch-test-apps';

export const useConnectedApps = (): ConnectedApp[] | null | false => {
    const featureFlag = useFeatureFlags();
    const notify = useNotify();
    const translate = useTranslate();
    const fetchConnectedApps = useFetchConnectedApps();
    const fetchApps = useFetchApps();
    const fetchTestApps = useFetchTestApps();
    const [connectedApps, setConnectedApps] = useState<ConnectedApp[] | null | false>(null);

    useEffect(() => {
        let mounted = true;

        if (!featureFlag.isEnabled('marketplace_activate')) {
            setConnectedApps([]);
            return;
        }

        (async () => {
            let connectedApps: ConnectedApp[] | null | false;

            try {
                connectedApps = await fetchConnectedApps();
                mounted && setConnectedApps(connectedApps);
            } catch (e) {
                mounted && setConnectedApps(false);
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.connect.connected_apps.list.flash.error')
                );
                return;
            }

            if (!connectedApps || connectedApps.length === 0) {
                return;
            }

            try {
                const apps = await fetchApps();
                const testApps = await fetchTestApps();

                setConnectedApps(state => {
                    if (state === null || state === false) {
                        return state;
                    }

                    return state.map(connectedApp => {
                        const app =
                            apps.apps.find(app => app.id === connectedApp.id) ||
                            testApps.apps.find(app => app.id === connectedApp.id);

                        return {
                            ...connectedApp,
                            activate_url: app?.activate_url || undefined,
                        };
                    });
                });
            } catch (e) {
                return;
            }
        })();

        return () => {
            mounted = false;
        };
    }, [fetchConnectedApps]);

    return connectedApps;
};
