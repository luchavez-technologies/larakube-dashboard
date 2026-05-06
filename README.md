# LaraKube System Dashboard
### The Official Web Counterpart for the LaraKube CLI

LaraKube Dashboard is a high-performance, professional control plane for managing Laravel applications on Kubernetes. Built to complement the [LaraKube CLI](https://larakube.luchtech.dev), it provides a real-time, data-rich interface for monitoring and managing your entire cluster.

## 🚀 Key Features

### 🌍 Global Cluster Monitor
A bird's-eye view of your entire infrastructure:
- **Active Projects:** Real-time health and status of all LaraKube-managed projects.
- **Recent Events:** Live stream of cluster-wide warnings and errors for proactive debugging.
- **Node Health:** Hardware-level visibility into CPU and RAM pressure across your Kubernetes nodes.

### 📦 Project Command Center
Deep, project-level integration within the `ProjectResource`:
- **Infrastructure Tabs:** Searchable, real-time tables for **Pods**, **Deployments**, **Services**, and **Ingresses**.
- **Live Log Streaming:** High-fidelity terminal UI for streaming pod logs with integrated pod selection.
- **Auto-Discovery:** Automatically detects and links Kubernetes namespaces to your CLI project data.

### 🛡️ Operational Reliability
- **RBAC Native:** Operates using a surgical `larakube-dashboard` ServiceAccount.
- **Filament v5:** Leverages the latest Filament Schema system for a dense, professional "Control Plane" aesthetic.
- **Saloon-Powered:** Robust Kubernetes API integration using the Saloon library.

## 🛠 Tech Stack
- **Backend:** Laravel 13.7 (PHP 8.5)
- **UI Framework:** Filament v5 (TALL Stack)
- **API Integration:** Saloon v4
- **Styling:** Tailwind CSS v4 & Professional Terminal CSS

## 📦 Distribution
This dashboard is designed to be distributed as a standalone Docker image and is automatically deployed via the `larakube dashboard --web` command.

---
Built with ❤️ by the **LaraKube Team**. [Read the Documentation](https://larakube.luchtech.dev)
