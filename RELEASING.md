# Releasing

## Overview

All 7 packages share the same version, set manually by the tech owner via git tag. There is no auto-versioning — the tag is the single source of truth.

## How Versioning Works

- All packages share the **same version** — a single tag (e.g., `v2.7.0`) releases all 7 packages at once.
- The **git tag is the source of truth**. There is no `version` field in `composer.json` — Packagist reads the tag directly.
- Tags follow [SemVer](https://semver.org/) with a `v` prefix: `v{major}.{minor}.{patch}` (e.g., `v2.7.0`).
- Version bumps are decided by the tech owner and tagged by a developer.

## Performing a Release

1. Tech owner requests a release and provides the version number.
2. Developer ensures `main` is in a releasable state — all CI checks pass, features are complete.
3. Create and push the tag:
   ```bash
   git tag v2.7.0
   git push origin v2.7.0
   ```
4. The `monorepo-split.yml` workflow triggers automatically:
   - Splits each package subtree via `git subtree split`
   - Pushes the subtree SHA + tag to all 7 mirror repos
   - Packages that don't exist at the tagged commit are skipped
5. Packagist picks up the new tags via webhooks — no manual action needed.

## Monorepo Split

Two workflows handle splitting:
- **`split-on-push.yml`** — runs on every push to `main`, syncs the latest code to mirror repos (untagged)
- **`monorepo-split.yml`** — runs on tag push (`v*`), pushes subtrees + the tag to all mirror repos

The mirror repos live under the same GitHub org as the monorepo (resolved via `github.repository_owner`). Authentication uses a GitHub App (`inpost-pay-management`) with Contents R/W permission only — credentials are loaded from 1Password at runtime.

## Troubleshooting

### Tag not triggering the workflow
- Ensure the tag starts with `v` (e.g., `v2.7.0`, not `2.7.0`)
- Ensure you pushed the tag: `git push origin v2.7.0`
- Check the Actions tab for the "Monorepo Split (Tag)" workflow run

### Split failed
- Verify `OP_SERVICE_ACCOUNT_TOKEN` secret is set on the repo
- Verify the GitHub App has access to the mirror repos
- Check the workflow logs for the specific error

### Packagist not updating
- Verify the webhook is configured on the mirror repo (Settings → Webhooks)
- Check Packagist dashboard for the package — look for webhook delivery errors
- Manually trigger an update on Packagist if needed

## Secrets

| Secret | Purpose |
|---|---|
| `OP_SERVICE_ACCOUNT_TOKEN` | 1Password service account for loading all other secrets at runtime |

All other credentials (GitHub App ID, private key) are stored in 1Password (`project-inpostpay` vault) and loaded via `1password/load-secrets-action`.
