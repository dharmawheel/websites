#!/bin/zsh

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <current_version> <next_version>"
    exit 1
fi

CURRENT_VERSION=$1
NEXT_VERSION=$2

if [ -z "$CURRENT_VERSION" ]; then
    echo "Current version is required"
    exit 1
fi

if [ -z "$NEXT_VERSION" ]; then
    echo "Next version is required"
    exit 1
fi

if [ "$CURRENT_VERSION" = "$NEXT_VERSION" ]; then
    echo "Current version and next version must be different"
    exit 1
fi

if [ ! -f $(git rev-parse --show-toplevel)/playbooks/update_phpbb.yml ]; then
    echo "playbooks/update_phpbb.yml not found"
    exit 1
fi

# Check version semver format
is_semver() {
    echo $1 | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+$'
}
if ! is_semver $CURRENT_VERSION; then
    echo "Current version is not a valid semver"
    exit 1
fi
if ! is_semver $NEXT_VERSION; then
    echo "Next version is not a valid semver"
    exit 1
fi

ansible-playbook \
    --extra-vars "update_phpbb_current_version=$CURRENT_VERSION update_phpbb_next_version=$NEXT_VERSION" \
    $(git rev-parse --show-toplevel)/playbooks/update_phpbb.yml
