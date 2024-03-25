import * as Sentry from "@sentry/browser";

if (window.SENTRY !== undefined && window.SENTRY !== null && window.SENTRY.DSN) {
    const isAuthenticated = window.USER !== undefined && window.USER !== null;
    const enableSentryTracing = window.SENTRY.TRACES_SAMPLE_RATE && window.SENTRY.TRACES_SAMPLE_RATE > 0;
    const firstPartyHostMatcher = new RegExp("https://(?:[\\w.]+\\.)?" + window.BASE.replaceAll(".", "\\."));

    const feedback = (window.SENTRY_FEEDBACK = new Sentry.Feedback({
        showName: !isAuthenticated,
        showEmail: !isAuthenticated,
        autoInject: true,
        colorScheme: "light",
        useSentryUser: {
            name: "name",
            email: "email",
        },
    }));

    Sentry.init({
        dsn: window.SENTRY.DSN,
        tunnel: window.SENTRY.TUNNEL,
        release: window.SENTRY.RELEASE,
        beforeSend(event, hint) {
            if (window.UNAUTHENTICATED_RELOAD_PENDING) {
                return;
            }

            return event;
        },
        environment: window.ENV,
        integrations: [
            feedback,
            ...(enableSentryTracing
                ? [
                      new Sentry.BrowserTracing({
                          tracingOrigins: [firstPartyHostMatcher],
                          beforeNavigate: (context) => {
                              return {
                                  ...context,
                                  name: location.pathname
                                      .replaceAll(/(\/)(@[a-zA-Z0-9-_]+)(\/|$)/g, "$1<username>$3")
                                      .replaceAll(/(\/)([a-f0-9-]{32,36})(\/|$)/g, "$1<uuid>$3")
                                      .replaceAll(/(\/team\/)([a-z0-9]{8})(\/|$)/g, "$1<slug>$3")
                                      .replaceAll(/(\/)(\d+)(\/|$)/g, "$1<id>$3"),
                              };
                          },
                      }),
                      new Sentry.Replay({
                          networkDetailAllowUrls: [firstPartyHostMatcher],
                      }),
                  ]
                : []),
        ],
        tracesSampleRate: enableSentryTracing ? window.SENTRY.TRACES_SAMPLE_RATE : 0.0,
        tracePropagationTargets: [firstPartyHostMatcher],
        replaysSessionSampleRate: enableSentryTracing ? window.SENTRY.REPLAYS_SAMPLE_RATE ?? 0.1 : 0.0,
        replaysOnErrorSampleRate: enableSentryTracing ? window.SENTRY.REPLAYS_ERROR_SAMPLE_RATE ?? 1.0 : 0.0,
    });

    if (isAuthenticated) {
        Sentry.configureScope((scope) => {
            scope.setUser({
                id: window.USER.id,
                name: window.USER.name,
                email: window.USER.email,
                chief_id: window.USER.chief_id,
            });
        });
    }
}

window.Sentry = Sentry;

require("./bootstrap");
