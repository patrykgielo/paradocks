# ClickUp Integration

**Status:** ✅ Active
**Agent:** `clickup-task-manager`
**MCP Server:** ClickUp MCP
**Last Updated:** 2025-12-26

---

## Overview

Automated task management integration with ClickUp via MCP (Model Context Protocol). This integration enables creating, organizing, and tracking development tasks directly from Claude Code terminal, eliminating context switching between development and project management tools.

### Key Features

- ✅ **Task Creation** - Create tasks/subtasks with intelligent defaults
- ✅ **Epic Breakdown** - Auto-generate task hierarchy from estimation docs
- ✅ **Git Sync** - Link commits to ClickUp tasks via commit messages
- ✅ **Time Tracking** - Start/stop timers, add manual entries
- ✅ **Smart Search** - Query tasks by assignee, status, keywords, dates
- ✅ **Templates** - Consistent task structure for epics, bugs, features
- ✅ **Custom Fields** - Story Points, Component, ADR references, PR links

### Benefits

| Benefit | Description |
|---------|-------------|
| **Reduced Context Switching** | Manage tasks without leaving terminal |
| **Consistency** | Standardized task structure via templates |
| **Automation** | Auto-sync Git commits, breakdown estimations |
| **Visibility** | Clear task hierarchy from planning to implementation |
| **Accuracy** | Precise time tracking for billing |

---

## Quick Start

### Prerequisites

1. **ClickUp Account** - Team workspace with admin access
2. **ClickUp API Token** - Generate at https://app.clickup.com/settings/apps
3. **Claude Code** - With ClickUp MCP server configured
4. **Environment Variable** - `CLICKUP_API_TOKEN=pk_...` in `.env`

### Setup Workflow

#### Step 1: Get Workspace Structure

```bash
@clickup-task-manager Show workspace structure
```

**Output:**
```
Workspace: "Your Team Workspace" (ID: 12345678)
  ├─ Space: "Paradocks Project" (ID: space_abc123)
  │   ├─ List: "Backlog" (ID: list_001)
  │   ├─ List: "Current Sprint" (ID: list_002)
  │   ├─ List: "Bugs" (ID: list_003)
  │   └─ List: "Done" (ID: list_004)
  └─ Space: "Other Project" (ID: space_xyz456)
```

Copy the IDs for next step.

#### Step 2: Create Space & Lists (if needed)

If "Paradocks Project" space doesn't exist:

1. Open ClickUp web UI: https://app.clickup.com
2. Create new Space: "Paradocks Project"
3. Create Lists within Space:
   - **Backlog** - Unscheduled tasks
   - **Current Sprint** - Active sprint tasks
   - **Bugs** - Bug tracking
   - **Done** - Completed tasks

#### Step 3: Create Custom Fields

In ClickUp Space Settings → Custom Fields:

| Field Name | Type | Options/Format | Purpose |
|------------|------|----------------|---------|
| **Story Points** | Number | Min: 0, Max: 100 | Effort estimate |
| **Component** | Dropdown | Backend, Frontend, DevOps, Docs, Testing | System component |
| **Related ADR** | Text | Format: ADR-XXX | Architecture Decision Record reference |
| **PR Link** | URL | GitHub PR URL | Pull Request link |
| **Git Branch** | Text | Format: feature/name | Feature branch name |

Copy the field IDs after creation (visible in field settings).

#### Step 4: Update Configuration

Edit `app/docs/clickup-sync.json`:

```json
{
  "workspace_id": "12345678",
  "default_space_id": "space_abc123",
  "default_lists": {
    "backlog": "list_001",
    "current_sprint": "list_002",
    "bugs": "list_003",
    "done": "list_004"
  },
  "custom_fields": {
    "story_points": "field_xyz789",
    "component": "field_abc456",
    "related_adr": "field_def123",
    "pr_link": "field_ghi789",
    "git_branch": "field_jkl012"
  },
  "task_templates": { ... },
  "commit_task_map": {},
  "last_updated": "2025-12-26T15:00:00Z",
  "version": "1.0"
}
```

**Important:** Remove the `_setup_instructions` key after setup complete.

#### Step 5: Test Integration

```bash
@clickup-task-manager Create task: Test ClickUp integration
```

**Expected Result:**
```
✅ Task Created

Task: [Test ClickUp integration](https://app.clickup.com/t/abc123)
List: Current Sprint
Status: To Do
Priority: Normal

Next Steps:
- View task in ClickUp web UI
- Assign to team member
- Set due date
```

If successful, you're ready to use the integration! 🎉

---

## Common Workflows

### 1. Create Epic from Estimation Document

**Use Case:** Convert Q1 estimation into ClickUp task hierarchy

**Command:**
```bash
@clickup-task-manager Create epic for Invoice PDF (Variant A)
```

**What Happens:**
1. Agent reads `docs/estimations/invoice-pdf-generation/wycena-szczegolowa.md`
2. Parses Variant A structure (5 phases, 45-50h total)
3. Creates epic task in ClickUp "Current Sprint" list
4. Creates 5 phase subtasks with time estimates
5. Creates component tasks under each phase
6. Sets custom fields (Story Points, Component)
7. Returns task URLs and hierarchy

**Output:**
```
✅ Epic Created from Estimation

Epic: [Invoice PDF Generation - Variant A](https://app.clickup.com/t/epic123)
List: Q1 2025 Sprint
Time Estimate: 45-50h
Story Points: 21

Phase Breakdown:

Phase 1: Foundation (14h)
- UserInvoiceProfile Model (6h) - SP: 3
- ValidNIP Rule (2h) - SP: 1
- Settings System (3h) - SP: 2
- Invoice Models (3h) - SP: 2

Phase 2: PDF Engine (16h)
- Invoice Number Generator (3h) - SP: 2
- PDF Template (10h) - SP: 5
- Storage & Download (3h) - SP: 2

... (full hierarchy)

Next Steps:
1. Review epic structure in ClickUp
2. Assign phase tasks to team members
3. Set sprint start date (Jan 6, 2025)
```

### 2. Git Commit Synchronization

**Use Case:** Automatically link commits to ClickUp tasks

**Commit Message Format:**
```bash
git commit -m "feat(invoice): implement PDF template (#ABC-123)"
```

**Supported Patterns:**
- `#ABC-123` - Task ID reference
- `(#123)` - Numeric ID
- `fixes #456` - Close task on merge
- `closes #XYZ-789` - Alternative close syntax

**Sync Command:**
```bash
@clickup-task-manager Sync latest commit to ClickUp
```

**What Happens:**
1. Parses commit message → extracts task ID (#ABC-123)
2. Gets commit metadata (SHA, author, branch, timestamp)
3. Searches ClickUp for task
4. Adds commit info as comment
5. Updates task status based on branch:
   - `feature/*` → "in progress"
   - `main` → "done"
   - `develop` → "code review"
6. Stores mapping in `clickup-sync.json`

**Output:**
```
✅ Commit Synced to ClickUp

Commit: abc123d - feat(invoice): implement PDF template
Task: [ABC-123 - Invoice PDF Template](https://app.clickup.com/t/ABC-123)
Status Updated: To Do → In Progress
Branch: feature/invoice-pdf-generation

Comment Added:
> ✅ Commit: feat(invoice): implement PDF template
> SHA: abc123d
> Branch: feature/invoice-pdf-generation
> Author: John Developer
> Time: 2025-12-26 15:45:00
```

### 3. Time Tracking

**Use Case:** Track time spent on tasks for billing

**Start Timer:**
```bash
@clickup-task-manager Start timer on ABC-123
```

**Output:**
```
⏱️ Timer Started

Task: [ABC-123 - Invoice PDF Template](https://app.clickup.com/t/ABC-123)
Started: 15:30:00
Status: In Progress

Use "Stop timer" when done.
```

**Stop Timer:**
```bash
@clickup-task-manager Stop timer
```

**Output:**
```
✅ Timer Stopped

Task: [ABC-123 - Invoice PDF Template](https://app.clickup.com/t/ABC-123)
Duration: 2h 15m
Started: 15:30:00
Stopped: 17:45:00

Total Time on Task: 5h 30m
Remaining Estimate: 4h 30m (of 10h total)
```

**Manual Time Entry:**
```bash
@clickup-task-manager Log 3 hours on ABC-123 for PDF template implementation
```

**Output:**
```
✅ Added 3h to ABC-123
Description: PDF template implementation
Billable: Yes
```

### 4. Search & Filter Tasks

**Use Case:** Find tasks quickly without opening ClickUp

**Examples:**

```bash
# My high-priority tasks
@clickup-task-manager Show my high-priority tasks

# Tasks in current sprint
@clickup-task-manager All in-progress tasks in Current Sprint

# Keyword search
@clickup-task-manager Find all invoice-related tasks

# Due date filter
@clickup-task-manager Tasks due this week
```

**Output:**
```
🔍 Search Results: High-Priority Tasks Assigned to Me

Found 2 tasks:

| Task | Priority | Status | Due Date | Time Logged | Remaining |
|------|----------|--------|----------|-------------|-----------|
| [ABC-123 - Invoice PDF](https://app.clickup.com/t/ABC-123) | 🔴 Urgent | In Progress | Dec 28 | 3h | 7h |
| [ABC-124 - Booking Fix](https://app.clickup.com/t/ABC-124) | 🟠 High | To Do | Dec 30 | 0h | 5h |

Quick Actions:
- Start timer: @clickup-task-manager Start timer on ABC-123
- Update status: @clickup-task-manager Update ABC-124 status to in progress
```

### 5. Create Simple Tasks

**Use Case:** Quick task creation for bugs or small features

**Bug Task:**
```bash
@clickup-task-manager Create bug: Booking wizard validation fails on empty NIP
```

**Output:**
```
✅ Bug Task Created

Task: [BUG] Booking wizard validation fails on empty NIP
List: Bugs
Status: To Do
Priority: Urgent
Tags: bug

Subtasks Created:
1. Reproduce issue - To Do
2. Root cause analysis - To Do
3. Implement fix - To Do
4. Add regression test - To Do
5. QA verification - To Do

Next Steps:
- Assign to frontend developer
- Set due date (recommend: today)
- Link to GitHub issue if reported
```

**Feature Task:**
```bash
@clickup-task-manager Create feature task: Add email preview in admin panel
```

**Output:**
```
✅ Feature Task Created

Task: [Add email preview in admin panel]
List: Current Sprint
Status: To Do
Priority: Normal
Tags: feature

Subtasks Created:
1. Design and planning - To Do
2. Implementation - To Do
3. Testing - To Do
4. Documentation - To Do
```

---

## Configuration Reference

### File: `docs/clickup-sync.json`

**Purpose:** Single source of truth for ClickUp integration

**Key Sections:**

#### workspace_id
```json
"workspace_id": "12345678"
```
Your ClickUp team workspace ID. Find in URL: `https://app.clickup.com/{workspace_id}/home`

#### default_lists
```json
"default_lists": {
  "backlog": "list_001",
  "current_sprint": "list_002",
  "bugs": "list_003",
  "done": "list_004"
}
```
Default list IDs for quick task creation. Used when user doesn't specify list.

#### task_templates
```json
"task_templates": {
  "feature_epic": {
    "name_pattern": "[EPIC] {feature_name}",
    "tags": ["epic", "feature"],
    "priority": 2,
    "subtask_template": [
      "Research and design",
      "Backend implementation",
      "Frontend implementation",
      "Testing and QA",
      "Documentation",
      "Code review and deployment"
    ]
  }
}
```
Reusable task structures for consistency. Agent applies template based on task type.

#### custom_fields
```json
"custom_fields": {
  "story_points": "field_xyz789",
  "component": "field_abc456",
  "related_adr": "field_def123",
  "pr_link": "field_ghi789",
  "git_branch": "field_jkl012"
}
```
Custom field ID mapping. Used to set field values on task creation.

#### commit_task_map
```json
"commit_task_map": {
  "a1b2c3d": "ABC-123",
  "e4f5g6h": "ABC-124"
}
```
Git commit SHA → ClickUp task ID mapping. Auto-populated by agent during Git sync.

---

## MCP Tools Reference

The agent uses these ClickUp MCP tools:

| Tool | Purpose | Example |
|------|---------|---------|
| `clickup_create_task` | Create new task/subtask | Create epic, bug, feature |
| `clickup_update_task` | Update task properties | Change status, assignees |
| `clickup_get_task` | Fetch task details | Get task by ID |
| `clickup_search` | Query tasks | Find by keyword, assignee |
| `clickup_create_task_comment` | Add comment | Sync commit info |
| `clickup_start_time_tracking` | Start timer | Begin work session |
| `clickup_stop_time_tracking` | Stop timer | End work session |
| `clickup_add_time_entry` | Manual time log | Add past work |
| `clickup_get_workspace_hierarchy` | List structure | Show spaces/lists |
| `clickup_get_list` | Get list metadata | Validate custom fields |
| `clickup_resolve_assignees` | Convert user refs | "me" → user ID |

**Documentation:** https://developer.clickup.com/docs

---

## Best Practices

### 1. Task Naming Conventions

**Use Prefixes:**
- `[EPIC]` - Large feature (multiple phases)
- `[BUG]` - Bug fix
- `[ESTIMATE]` - Estimation breakdown
- No prefix - Standard feature task

**Be Descriptive:**
```
✅ Good: "Invoice PDF Generation - Add NIP validation"
❌ Bad: "Fix validation"

✅ Good: "Booking wizard - Implement time slot selection"
❌ Bad: "Add time slots"
```

### 2. Commit Message Format

**Follow Conventional Commits:**
```
feat(scope): description (#TASK-ID)
fix(scope): description (#TASK-ID)
docs: description (#TASK-ID)
refactor(scope): description (#TASK-ID)
test: description (#TASK-ID)
```

**Examples:**
```
✅ feat(invoice): add PDF template (#ABC-123)
✅ fix(booking): validate NIP checksum (fixes #ABC-456)
✅ docs: update ClickUp integration README (#ABC-789)
```

### 3. Time Tracking Discipline

**Always track time:**
- Start timer when beginning work
- Stop timer when switching tasks
- Log manual entries for offline work
- Add descriptions for context

**Weekly review:**
```bash
@clickup-task-manager Weekly time report for me
```

### 4. Use Templates

**Don't create tasks from scratch:**
- Epic → Use `feature_epic` template (automatic)
- Bug → Use `bug_fix` template (automatic)
- Feature → Use `feature_task` template

**Customize templates:**
Edit `docs/clickup-sync.json` → `task_templates` to match your workflow

### 5. Search Before Creating

**Avoid duplicates:**
```bash
# Search first
@clickup-task-manager Find invoice PDF tasks

# Then create if not found
@clickup-task-manager Create epic for Invoice PDF
```

---

## Troubleshooting

### ⚠️ "ClickUp sync not configured"

**Problem:** `clickup-sync.json` has TBD values

**Fix:**
1. Run: `@clickup-task-manager Show workspace structure`
2. Copy workspace_id, space_id, list IDs
3. Edit `docs/clickup-sync.json` with real IDs
4. Remove `_setup_instructions` section

### ⚠️ "List not found"

**Problem:** list_id in config is invalid or changed

**Fix:**
1. Run: `@clickup-task-manager Show workspace structure`
2. Find correct list ID
3. Update `docs/clickup-sync.json` → `default_lists.{list_name}`

### ⚠️ "Invalid custom field"

**Problem:** Custom field ID doesn't exist or wrong for this list

**Fix:**
1. In ClickUp: Space Settings → Custom Fields
2. Find field, click settings, copy ID
3. Update `docs/clickup-sync.json` → `custom_fields.{field_name}`

**Note:** Custom field IDs are list-specific. If creating tasks in different lists, field IDs may differ.

### ⚠️ "Rate limit exceeded"

**Problem:** Hit ClickUp API limit (100 req/min)

**Fix:**
- Wait 60+ seconds before retrying
- Agent auto-retries with exponential backoff
- Reduce bulk operations frequency
- Use cache (don't repeatedly fetch same data)

### ⚠️ "Task not found"

**Problem:** Task ID doesn't exist or was deleted

**Fix:**
```bash
# Search by name
@clickup-task-manager Search for {task_name}

# Check if moved to different workspace/space
@clickup-task-manager Show workspace structure
```

### ⚠️ Authentication Error

**Problem:** Invalid ClickUp API token

**Fix:**
1. Generate new token: https://app.clickup.com/settings/apps
2. Update `.env`: `CLICKUP_API_TOKEN=pk_...`
3. Restart Claude Code

---

## Limitations

### ClickUp API Constraints

- **Rate Limit:** 100 requests/minute per token (team-wide)
- **Subtask Depth:** Maximum 6 levels of nesting
- **Custom Fields:** List-specific IDs (can't share across lists)
- **Bulk Operations:** Not all endpoints support bulk create/update

### Agent Limitations

- **No Webhooks (v1):** Real-time ClickUp → Git sync not yet implemented
- **No Dependencies (v1):** Task blocking/waiting relationships not managed
- **No Recurring Tasks (v1):** Scheduled repetition not supported
- **Manual Assignee Resolution:** Agent uses "me" or user IDs, not names yet

### Future Enhancements

**Planned but not yet implemented:**
- Webhook integration (two-way sync)
- Dependency management
- Recurring task creation
- Custom dashboards
- Slack/Discord notifications
- GitHub Actions integration
- Bulk imports from CSV/JSON

---

## Examples Library

### Epic Breakdown from Scratch

**Scenario:** Create epic manually without estimation docs

```bash
@clickup-task-manager Create epic: Customer Profile Management System

Description: Allow customers to manage their profile, vehicle info, and preferences

Phases:
1. User Profile UI (8h)
2. Vehicle Management (6h)
3. Notification Preferences (4h)
4. Testing & Documentation (4h)

Tags: epic, q1-2025, customer-profile
Priority: High
```

### Update Task After PR Merge

**Scenario:** Mark task done after PR merged to main

```bash
# Git workflow
git checkout main
git merge feature/invoice-pdf-generation
git push origin main

# ClickUp sync
@clickup-task-manager Update ABC-123 status to done, add PR link https://github.com/org/repo/pull/456
```

### Bulk Time Entry

**Scenario:** Log time for multiple tasks after offline work

```bash
@clickup-task-manager Log 2h on ABC-123 for PDF template
@clickup-task-manager Log 1.5h on ABC-124 for NIP validation
@clickup-task-manager Log 3h on ABC-125 for Filament admin panel
```

### Sprint Planning

**Scenario:** Create all tasks for upcoming sprint

```bash
# Create sprint list (if not exists)
@clickup-task-manager Create list: Sprint 2 (Jan 20 - Feb 2)

# Create epics
@clickup-task-manager Create epic: Email System Enhancements
@clickup-task-manager Create epic: Booking Wizard Refactor
@clickup-task-manager Create epic: Performance Optimization

# Breakdown each epic
@clickup-task-manager Break down Email System epic from estimation docs
```

---

## References

### Documentation

- **ClickUp API Docs:** https://developer.clickup.com/docs
- **MCP Documentation:** https://modelcontextprotocol.io/
- **Agent File:** `.claude/agents/clickup-task-manager.md`
- **Config File:** `docs/clickup-sync.json`
- **Claude Code Guide:** https://claude.com/claude-code

### Related Features

- **Project Coordinator Agent:** `.claude/agents/project-coordinator.md`
- **Commercial Estimator Agent:** `.claude/agents/commercial-estimate-specialist.md`
- **Laravel Senior Architect:** `.claude/agents/laravel-senior-architect.md`

### Support

**Questions or issues?**
- Check agent file for detailed workflows
- Review troubleshooting section above
- Check ClickUp API status: https://status.clickup.com/
- Report bugs via GitHub issues

---

**Version:** 1.0
**Last Updated:** 2025-12-26
**Maintained By:** Paradocks Project Team
**Feedback Welcome:** Open GitHub issue or PR for improvements
