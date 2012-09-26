== QARC

This script serves to keep the directory your webserver is serving
at the most recent commit to your repo.

At Grooveshark on the QA team, we use this to make available the
most recent version of our app for testing.

== Getting Started

1. Ensure you've got a working webserver going with support for PHP and Gearman

2. Install and configure GIT

3. CHOWN the files in your repo to your webserver user

4. Place your endpoint someplace accessible on the internet
