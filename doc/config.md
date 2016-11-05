# Bundle-dependent configuration loading

The kernel checks if files are in place with the same naming convention as their DI extension would be, in the 
`app/config/bundles/` directory. This way, bundles can easily be enabled and disabled, without the need to change the
configuration file's contents.

# Local configuration overrides

If you need local configuration different from your colleagues, you can add `app/config/config_local.yml`, which is
always preferred over any environment-dependent configuration file

# Debugging defaults

The debug parameter is based on the environment by default, rather than on the 'debug' flag.
