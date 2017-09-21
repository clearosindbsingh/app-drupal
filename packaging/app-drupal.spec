
Name: app-drupal
Epoch: 1
Version: 1.0.0
Release: 1%{dist}
Summary: **drupal_app_name**
License: GPL
Group: ClearOS/Apps
Packager: Xtreem Solution
Vendor: Xtreem Solution
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-web-server
Requires: app-mariadb
Requires: unzip
Requires: zip

%description
**drupal_app_description**

%package core
Summary: **drupal_app_name** - Core
License: GPL
Group: ClearOS/Libraries
Requires: app-base-core
Requires: mod_authnz_external
Requires: mod_authz_unixgroup
Requires: mod_ssl
Requires: phpMyAdmin
Requires: app-flexshare-core

%description core
**drupal_app_description**

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/drupal
cp -r * %{buildroot}/usr/clearos/apps/drupal/

install -d -m 0775 %{buildroot}/var/clearos/drupal
install -d -m 0775 %{buildroot}/var/clearos/drupal/backup
install -d -m 0775 %{buildroot}/var/clearos/drupal/sites
install -d -m 0775 %{buildroot}/var/clearos/drupal/versions
install -D -m 0644 packaging/app-drupal.conf %{buildroot}/etc/httpd/conf.d/app-drupal.conf

%post
logger -p local6.notice -t installer 'app-drupal - installing'

%post core
logger -p local6.notice -t installer 'app-drupal-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/drupal/deploy/install ] && /usr/clearos/apps/drupal/deploy/install
fi

[ -x /usr/clearos/apps/drupal/deploy/upgrade ] && /usr/clearos/apps/drupal/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-drupal - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-drupal-core - uninstalling'
    [ -x /usr/clearos/apps/drupal/deploy/uninstall ] && /usr/clearos/apps/drupal/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/drupal/controllers
/usr/clearos/apps/drupal/htdocs
/usr/clearos/apps/drupal/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/drupal/packaging
%exclude /usr/clearos/apps/drupal/unify.json
%dir /usr/clearos/apps/drupal
%dir %attr(0775,webconfig,webconfig) /var/clearos/drupal
%dir %attr(0775,webconfig,webconfig) /var/clearos/drupal/backup
%dir %attr(0775,webconfig,webconfig) /var/clearos/drupal/sites
%dir %attr(0775,webconfig,webconfig) /var/clearos/drupal/versions
/usr/clearos/apps/drupal/deploy
/usr/clearos/apps/drupal/language
/usr/clearos/apps/drupal/libraries
/etc/httpd/conf.d/app-drupal.conf
