# Releasing

## Overview

All 7 packages share the same version, set manually by the tech owner via git tag. There is no auto-versioning — the tag is the single source of truth.

```mermaid
flowchart TD
    Dev["Developer merges PR to main"]
    TO["Tech Owner requests release"]
    Tag["Developer pushes tag<br/><code>git tag v2.7.0 && git push origin v2.7.0</code>"]

    TO --> Tag

    Dev --> Split1["splitsh/lite<br/><i>split-on-push.yml</i>"]
    Tag --> Split2["splitsh/lite<br/><i>monorepo-split.yml</i>"]

    Split1 --> |"7 parallel jobs"| SplitPush["Push subtree → module repo<br/><i>inpost/magento2-module-*</i><br/>(main branch, no tag)"]

    Split2 --> |"7 parallel jobs"| SplitTag["Push subtree + tag → module repo<br/><i>inpost/magento2-module-*</i>"]
    SplitTag --> Packagist["Packagist picks up tag<br/>via webhook (×7)"]

    style TO fill:#6f42c1,color:#fff
    style Dev fill:#4a90d9,color:#fff
    style Tag fill:#2ea44f,color:#fff
    style Packagist fill:#f7b731,color:#000
    style SplitPush fill:#6c757d,color:#fff
    style SplitTag fill:#2ea44f,color:#fff
```

## Release Lifecycle (Sequence)

```mermaid
sequenceDiagram
    actor TO as Tech Owner
    actor Dev as Developer
    participant GH as GitHub Monorepo<br/>(main branch)
    participant SL as splitsh/lite
    participant MR as Module Repos<br/>(1 per module)
    participant PK as Packagist

    Note over TO,PK: Phase 1 — Feature development

    Dev->>GH: create feature branch
    Dev->>Dev: implement changes
    Dev->>GH: open Pull Request
    Dev->>GH: merge PR to main
    activate GH
    GH->>SL: split-on-push.yml triggers
    loop 7 module repos
        SL->>MR: push subtree SHA → main (untagged)
    end
    deactivate GH

    Note over TO,PK: Phase 2 — Release

    TO->>Dev: request release v2.7.0
    Dev->>Dev: verify main is releasable
    Dev->>GH: git tag v2.7.0 && git push origin v2.7.0
    activate GH
    GH->>SL: monorepo-split.yml triggers (on tag v*)
    loop 7 module repos
        SL->>MR: push subtree SHA → main
        SL->>MR: push tag v2.7.0
        MR->>PK: webhook notifies new tag
    end
    PK->>PK: index all 7 packages at v2.7.0

    Note over TO,PK: Consumers can now: composer require inpost/magento2-module-inpost-pay:^2.7
```

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
   - Splits each package subtree via splitsh/lite
   - Pushes the subtree + tag `v2.7.0` to all 7 split repos under `inpost/`
5. Packagist picks up the new tags via webhooks — no manual action needed.

## Monorepo Split (splitsh/lite)

The split process uses [splitsh/lite](https://github.com/splitsh/lite), the industry-standard subtree splitting tool by Fabien Potencier (used by Symfony for 50+ packages).

Two workflows handle splitting:
- **`split-on-push.yml`** — runs on every push to `main`, syncs the latest code to split repos (untagged)
- **`monorepo-split.yml`** — runs on tag push (`v*`), pushes subtrees + the tag to all split repos

The split org is `inpost` — each package maps to `inpost/magento2-module-*`.

## Troubleshooting

### Tag not triggering the workflow
- Ensure the tag starts with `v` (e.g., `v2.7.0`, not `2.7.0`)
- Ensure you pushed the tag: `git push origin v2.7.0`
- Check the Actions tab for the "Monorepo Split" workflow run

### Split failed
- Verify `SPLIT_REPO_ACCESS_TOKEN` secret is set and has `repo` scope
- Verify the target repo exists under the `inpost` org (e.g., `inpost/magento2-module-inpost-pay`)
- Check the split workflow logs for `splitsh-lite` output

### Packagist not updating
- Verify the webhook is configured on the split repo (Settings → Webhooks)
- Check Packagist dashboard for the package — look for webhook delivery errors
- Manually trigger an update on Packagist if needed

## Secrets Reference

| Secret | Purpose | Scope |
|---|---|---|
| `SPLIT_REPO_ACCESS_TOKEN` | PAT for pushing to split repos | `repo` scope, access to `inpost` org |
| `COMPOSER_AUTH` | Composer authentication for CI (private packages) | CI only |
| `HYVA_AUTH` | Hyva Composer repository credentials | CI only |

## Demo / Training Environment

A `fwc-demo` org can be used for testing the full release cycle without affecting production repos. See `scripts/setup-demo-org.sh` for automated setup.

To run a test release cycle:
1. Run the setup script to create demo split repos
2. Fork the monorepo to your account / the demo org
3. Update `inpost` → `fwc-demo` in the workflow files
4. Set `SPLIT_REPO_ACCESS_TOKEN` secret on the fork
5. Push a commit to main, then tag it:
   ```bash
   git commit --allow-empty -m 'test release cycle'
   git push origin main
   git tag v0.0.1-test
   git push origin v0.0.1-test
   ```
