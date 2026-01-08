# RDX Platform - Admin Guide

This comprehensive guide covers all administrative functions and features of the RDX Platform.

---

## Table of Contents

1. [Admin Access](#admin-access)
2. [Dashboard Overview](#dashboard-overview)
3. [User Management](#user-management)
4. [Loan Management](#loan-management)
5. [Share Management](#share-management)
6. [Transaction Management](#transaction-management)
7. [VTU Service Management](#vtu-service-management)
8. [System Settings](#system-settings)
9. [Analytics & Reporting](#analytics--reporting)
10. [Roles & Permissions](#roles--permissions)

---

## Admin Access

### Accessing Admin Panel

1. Log in with admin credentials
2. Click "Admin Dashboard" from user menu
3. Or navigate to `/admin`

### Admin Roles

| Role | Access Level | Permissions |
|------|--------------|-------------|
| **Super Admin** | Full access | All permissions |
| **Admin** | High access | Most features except critical settings |
| **Manager** | Limited access | View and basic operations |

---

## Dashboard Overview

The admin dashboard (`/admin`) provides:

**Key Metrics:**
- Total registered users
- Total loans (active/pending counts)
- Total shares (approved/pending counts)
- Total wallet balance across platform

**Recent Activity:**
- Recent user registrations
- Recent loan applications
- Recent transactions

**Quick Actions:**
- Create loan
- Create dividend
- Manage users
- View analytics

---

## User Management

### Viewing All Users

**Path:** Admin → Users

**Features:**
- Search users by name/email
- Filter by status, role, registration date
- View user details
- Manage user accounts

### User Details

Click on any user to view:
- **Profile Information:** Name, email, phone, avatar
- **Account Status:** Active, suspended, verified
- **Financial Summary:**
  - Wallet balance
  - Total shares
  - Active loans
  - Transaction history
- **Loan Level:** Current level and upgrade progress

### User Actions

**Available Actions:**
- View full profile
- View transaction history
- View loans
- View shares
- Suspend/Activate account
- Reset password (send reset email)
- Adjust wallet balance (manual adjustment)

### Creating Users Manually

1. Go to Users → Create User (if available)
2. Enter user details
3. Assign role
4. Set initial password
5. Send welcome email

---

## Loan Management

### Viewing All Loans

**Path:** Admin → Loans

**Filters:**
- Status (Pending, Active, Paid, Defaulted)
- Date range
- User search
- Loan level

### Loan Details

Click on any loan to view:
- Borrower information
- Loan amount and terms
- Interest rate and duration
- Payment schedule
- Payment history
- Current balance
- Status

### Creating Loans Manually

**Path:** Admin → Loans → Create Loan

1. Select user
2. Enter loan amount
3. Select loan level
4. Set interest rate (or use default)
5. Set duration (months)
6. Review calculated terms:
   - Monthly payment
   - Total repayment
   - Payment schedule
7. Click "Create Loan"
8. Loan is created in approved status

### Loan Actions

- **Approve:** Approve pending loan
- **Reject:** Reject loan application
- **Disburse:** Release funds to user wallet
- **Mark as Defaulted:** Flag overdue loans
- **View Payment Schedule:** See full amortization
- **Record Manual Payment:** Add offline payment

### Loan Level Management

**Path:** Admin → Loan Levels

**Features:**
- View all loan levels
- Edit level details:
  - Maximum loan amount
  - Interest rate
  - Installment period
  - Repayments required for upgrade
- Create new levels
- Activate/deactivate levels

**Automatic Upgrades:**
- System automatically upgrades users based on successful repayments
- Configure thresholds in Loan Settings

---

## Share Management

### Viewing All Shares

**Path:** Admin → Shares

**Information Displayed:**
- User/holder
- Quantity owned
- Status (Approved, Pending, Rejected)
- Purchase date
- Current value

### Share Actions

- **View Details:** See full share information
- **Approve:** Approve pending share purchases
- **Reject:** Reject share purchases
- **Manual Adjustment:** Add/remove shares manually

### Creating Shares Manually

**Path:** Admin → Shares → Create Share

1. Select user
2. Enter quantity
3. Set status (Approved/Pending)
4. Add notes
5. Click "Create"

### Dividend Management

**Path:** Admin → Dividends

#### Creating Dividends

1. Go to Dividends → Create Dividend
2. Enter dividend details:
   - Title/Description
   - Rate per share
   - Total amount
   - Distribution date
3. Click "Create Dividend"
4. System calculates individual payments

#### Dividend Distribution

**Manual Process:**
1. View dividend details
2. Review calculated payments
3. Click "Distribute"
4. Payments credited to user wallets
5. Email notifications sent

**Dividend History:**
- View all past dividends
- See distribution status
- Export dividend reports

---

## Transaction Management

### Viewing All Transactions

**Path:** Admin → Transactions

**Filters:**
- Transaction type
- Status (Success, Failed, Pending)
- Date range
- User search
- Amount range

### Transaction Types

- Wallet funding (Paystack)
- Withdrawals
- Transfers
- Share purchases/sales
- Loan disbursements
- Loan payments
- Service purchases (VTU)
- Dividend payments

### Transaction Details

Click on transaction to view:
- Transaction ID and reference
- User information
- Type and status
- Amount and fees
- Date and time
- Related records (loan, share, etc.)
- Payment gateway response (if applicable)

### Transaction Actions

- **View Details:** Full transaction information
- **Refund:** Process refund (if applicable)
- **Mark as Failed:** Update failed transactions
- **Export:** Download transaction data

---

## VTU Service Management

Manage all VTU service plans and transactions.

### Airtime Management

**Path:** Admin → Airtime

**Features:**
- View all airtime transactions
- Create airtime plans
- Edit plan details (network, amounts)
- Activate/deactivate plans
- View transaction history

### Data Management

**Path:** Admin → Data

**Features:**
- View all data transactions
- Manage data plans by network
- Set plan prices and validity
- Monitor data purchase trends

### Cable TV Management

**Path:** Admin → Cable

**Features:**
- Manage cable providers (DSTV, GOTV, etc.)
- Configure subscription packages
- View subscription history
- Handle failed transactions

### Electricity Management

**Path:** Admin → Electricity

**Features:**
- Manage electricity discos
- View meter recharge history
- Handle token delivery issues
- Monitor transaction success rates

### Education Pins Management

**Path:** Admin → Education

**Features:**
- Manage exam types (WAEC, NECO, etc.)
- Set pin prices
- View pin purchase history
- Track pin delivery

---

## System Settings

### General Settings

**Path:** Admin → Settings → General

Configure:
- Platform name
- Contact information
- Support email
- Platform logo
- Maintenance mode

### Share Settings

**Path:** Admin → Settings → Shares

Configure:
- **Share Price:** Current price per share
- **Minimum Purchase:** Minimum shares per transaction
- **Maximum Purchase:** Maximum shares per transaction
- **Require Approval:** Enable/disable admin approval for purchases

### Loan Settings

**Path:** Admin → Settings → Loans

Configure:
- **Minimum Loan Amount**
- **Maximum Loan Amount**
- **Default Interest Rate**
- **Minimum Duration:** Months
- **Maximum Duration:** Months
- **Auto-Approval Threshold:** Amount for instant approval
- **Require Guarantor:** Enable/disable guarantor requirement
- **Minimum Guarantors:** Number required
- **Late Payment Penalty:** Percentage
- **Grace Period:** Days before penalty

**Loan Level Thresholds:**
- Configure repayments required for each level
- Set maximum amounts per level
- Adjust interest rates by level
- Set installment periods

### Wallet Settings

**Path:** Admin → Settings → Wallet

Configure:
- **Minimum Funding Amount**
- **Maximum Funding Amount**
- **Minimum Withdrawal Amount**
- **Maximum Withdrawal Amount**
- **Withdrawal Fee:** Percentage or fixed
- **Transfer Fee:** Percentage or fixed
- **Auto-Approve Withdrawals:** Enable/disable

### Layout Settings

**Path:** Admin → Settings → Layout

Configure:
- Theme settings
- Navigation menu items
- Footer content
- Homepage sections

---

## Analytics & Reporting

### Admin Analytics Dashboard

**Path:** Admin → Analytics

**Available Charts:**

1. **User Growth**
   - New registrations over 12 months
   - Line chart visualization

2. **Loans by Status**
   - Distribution across statuses
   - Doughnut chart

3. **Transaction Volume**
   - By transaction type (last 30 days)
   - Bar chart

4. **Loan Repayments**
   - Monthly repayment trends (6 months)
   - Bar chart

5. **Share Purchases**
   - Purchase activity over 12 months
   - Dual-axis line chart

**Revenue Overview:**
- Loan interest earned
- Share purchases total
- Service transaction volume

### Exporting Reports

**Available Exports:**
- Transaction reports (CSV/Excel)
- User reports
- Loan reports
- Share reports
- Financial statements

**Export Steps:**
1. Navigate to relevant section
2. Apply filters
3. Click "Export"
4. Select format (CSV/Excel/PDF)
5. Download file

---

## Roles & Permissions

### Managing Roles

**Path:** Admin → Roles

**Default Roles:**
- Super Admin
- Admin
- Manager
- User

### Creating Custom Roles

1. Go to Roles → Create Role
2. Enter role name
3. Select permissions:
   - View users
   - Edit users
   - Delete users
   - Manage loans
   - Manage shares
   - Manage transactions
   - Manage settings
   - View analytics
4. Click "Create Role"

### Managing Permissions

**Path:** Admin → Permissions

**Permission Categories:**
- User Management
- Loan Management
- Share Management
- Transaction Management
- VTU Services
- Settings
- Analytics
- Roles & Permissions

### Assigning Roles to Users

1. Go to Users
2. Click on user
3. Select "Edit Role"
4. Choose role from dropdown
5. Click "Update"

---

## Best Practices

### Daily Tasks

✅ **Morning:**
- Review pending loan applications
- Check withdrawal requests
- Monitor failed transactions
- Review new user registrations

✅ **Afternoon:**
- Process approved withdrawals
- Respond to support tickets
- Review analytics dashboard

✅ **Evening:**
- Check system health
- Review day's transaction volume
- Plan next day's tasks

### Weekly Tasks

- Generate weekly reports
- Review loan default risks
- Analyze user growth trends
- Update service plans if needed
- Review and adjust settings

### Monthly Tasks

- Create and distribute dividends
- Generate monthly financial reports
- Review loan level configurations
- Analyze platform performance
- Plan system improvements

---

## Troubleshooting

### Common Issues

**Issue:** User can't log in
**Solution:**
1. Verify account is active
2. Check email verification status
3. Reset password if needed
4. Check for IP blocks

**Issue:** Transaction failed
**Solution:**
1. Check transaction details
2. Verify payment gateway response
3. Check user wallet balance
4. Retry or refund as appropriate

**Issue:** Loan application rejected
**Solution:**
1. Check eligibility criteria
2. Verify share holdings
3. Check for active loans
4. Review loan level limits

**Issue:** Share purchase pending
**Solution:**
1. Review share approval settings
2. Approve or reject manually
3. Notify user of decision

---

## Security Guidelines

🔒 **Admin Security:**
- Use strong, unique passwords
- Enable 2FA on admin accounts
- Log out when not in use
- Don't share admin credentials
- Review admin activity logs regularly
- Restrict admin access by IP (if available)

🔒 **Data Protection:**
- Regular database backups
- Secure sensitive user data
- Comply with data protection regulations
- Monitor for suspicious activity
- Implement access controls

---

## System Maintenance

### Backup Procedures

**Recommended Schedule:**
- **Daily:** Database backups
- **Weekly:** Full system backups
- **Monthly:** Backup verification

**Backup Storage:**
- Keep multiple backup copies
- Store off-site
- Test restore procedures regularly

### Update Procedures

**Before Updating:**
1. Create full backup
2. Test in staging environment
3. Schedule maintenance window
4. Notify users

**During Update:**
1. Enable maintenance mode
2. Run migrations
3. Clear caches
4. Test critical features

**After Update:**
1. Verify all features working
2. Check error logs
3. Disable maintenance mode
4. Monitor for issues

---

## Support & Resources

**Technical Support:**
- Developer documentation
- Laravel documentation
- Livewire documentation
- Paystack API docs

**Platform Support:**
- Email: admin@rdxplatform.com
- Emergency contact: [Phone Number]

---

## Appendix

### Keyboard Shortcuts

- `Ctrl + K`: Quick search
- `Ctrl + /`: Command palette
- `Esc`: Close modals

### API Endpoints

(If API is available)
- Authentication: `/api/auth`
- Users: `/api/users`
- Loans: `/api/loans`
- Shares: `/api/shares`
- Transactions: `/api/transactions`

### Database Tables

Key tables for reference:
- `users`
- `loans`
- `loan_levels`
- `loan_payments`
- `shares`
- `dividends`
- `dividend_payments`
- `transactions`
- `wallets`
- `wallet_transactions`

---

**Remember:** With great power comes great responsibility. Use admin privileges wisely! 🛡️
