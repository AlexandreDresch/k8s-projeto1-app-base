# Full-Stack Kubernetes Deployment Example
This project demonstrates a full-stack application deployment on Kubernetes, featuring a PHP backend, a MySQL database, and an Nginx-served HTML/CSS/JS frontend. The application allows users to submit feedback (name, email, comment) which is then stored in a MySQL database.

## 🚀 Project Overview
The application consists of three main components:
- **Frontend**: A simple HTML form styled with CSS and interactive JavaScript (jQuery) for submitting feedback. Served by Nginx.
- **Backend**: A PHP script that receives form submissions via POST request and inserts the data into a MySQL database.
- **Database**: A MySQL 8.0 instance to store the submitted feedback messages.

All components are containerized using Docker and orchestrated with Kubernetes for production readiness.

## 📁 Project Structure.
```bash
├── backend/
│   ├── conexao.php           # PHP database connection
│   ├── index.php             # PHP backend logic (handles form submission)
│   └── Dockerfile            # Dockerfile for PHP backend
├── frontend/
│   ├── css.css               # Frontend styles
│   ├── index.html            # Frontend HTML form
│   ├── js.js                 # Frontend JavaScript (AJAX submission)
│   ├── Dockerfile            # Dockerfile for Nginx frontend
│   └── nginx.conf            # Nginx configuration for frontend
├── kubernetes/
│   ├── mysql-deployment.yaml # Kubernetes Deployment for MySQL
│   ├── mysql-service.yaml    # Kubernetes Service for MySQL (ClusterIP)
│   ├── php-backend-deployment.yaml # Kubernetes Deployment for PHP backend
│   ├── php-backend-service.yaml # Kubernetes Service for PHP backend (ClusterIP)
│   ├── nginx-frontend-deployment.yaml # Kubernetes Deployment for Nginx frontend
│   └── nginx-frontend-service.yaml # Kubernetes Service for Nginx frontend (LoadBalancer)
└── README.md                 # This README file
```

## 📋 Prerequisites
Before you begin, ensure you have the following installed on your system:
- Docker: Used to build the application's container images.
- kubectl: The command-line tool for interacting with your Kubernetes cluster.
- Kubernetes Cluster:
    - Minikube (Recommended for local development): A tool that runs a single-node Kubernetes cluster on your local machine.
    - Alternatively, you can use a cloud-based Kubernetes cluster (e.g., GKE, EKS, AKS) configured with kubectl.
    
## 🚀 Deployment Steps
Follow these steps to get the application up and running on your Kubernetes cluster.
1. Start Your Kubernetes Cluster (if using Minikube)
    ```bash
        minikube start
    ```
2. Build Docker Images: Navigate to the backend and frontend directories to build their respective Docker images.
    ```bash
        # Build Backend Image
        cd backend
        docker build -t php-backend-image:1.0 .
        cd ..
    ```
    ```bash
        # Build Frontend Image
        cd frontend
        docker build -t nginx-frontend-image:1.0 .
        cd ..
    ```
3. Load Images into Minikube (if using Minikube)
If you are using Minikube, you need to load the newly built images into the Minikube Docker daemon so that Kubernetes can find them.
    ```bash
        minikube image load php-backend-image:1.0
        minikube image load nginx-frontend-image:1.0
    ```
4. Deploy to KubernetesNavigate to the `kubernetes/` directory and apply the YAML files in the specified order. This order is important to ensure dependencies (like MySQL being available for the backend) are met.
    ```bash
        cd kubernetes/

        # 1. Deploy MySQL Database
        kubectl apply -f mysql-deployment.yaml
        kubectl apply -f mysql-service.yaml
    ```
Wait for the MySQL pod to be in the `Running` state before proceeding. You can check its status with:
```bash
    kubectl get pods -l app=mysql
```
The output should show `STATUS` as `Running`.

5. Initialize MySQL Database (Create Table)

Once the MySQL pod is running, you need to create the `mensagens` table.

First, get the exact name of your MySQL pod:
```bash
    kubectl get pods -l app=mysql
```

(Example output: `mysql-deployment-789abcdef-ghijk`)

Then, execute the SQL command inside the MySQL pod to create the table:
```bash
    kubectl exec -it <YOUR_MYSQL_POD_NAME> -- mysql -uroot -pSenha123 meubanco -e "CREATE TABLE IF NOT EXISTS mensagens (id INT PRIMARY KEY, nome VARCHAR(255), email VARCHAR(255), comentario TEXT);"
```

Replace `<YOUR_MYSQL_POD_NAME>` with the actual name you got from the previous `kubectl get pods` command.
```bash
    # 2. Deploy PHP Backend
    kubectl apply -f php-backend-deployment.yaml
    kubectl apply -f php-backend-service.yaml
```

Wait for the PHP backend pod to be in the `Running` state:
```bash
    kubectl get pods -l app=php-backend
```

```bash
    # 3. Deploy Nginx Frontend
    kubectl apply -f nginx-frontend-deployment.yaml
    kubectl apply -f nginx-frontend-service.yaml
```

Wait for the Nginx frontend pod to be in the Running state:
```bash
    kubectl get pods -l app=nginx-frontend
```

## 🌐 Access the Application
### If using Minikube:
Minikube can expose services directly. Get the URL for the frontend service:
```bash
    minikube service nginx-frontend-service --url
```
This command will output the URL (e.g., `http://192.168.49.2:30000`). Open this URL in your web browser.

### If using a Cloud Kubernetes Cluster:
The `nginx-frontend-service` is of type `LoadBalancer`. Your cloud provider will provision an external IP address or hostname for it. This might take a few minutes.

Check the external IP:
```bash
    kubectl get service nginx-frontend-service
```

Look for the `EXTERNAL-IP` column. Once an IP is assigned, you can access your application via `http://<EXTERNAL-IP>`.

## 🧹 Clean Up
To remove all the deployed Kubernetes resources from your cluster:
```bash
    cd kubernetes/ # Ensure you are in the kubernetes directory

    kubectl delete -f nginx-frontend-service.yaml
    kubectl delete -f nginx-frontend-deployment.yaml
    kubectl delete -f php-backend-service.yaml
    kubectl delete -f php-backend-deployment.yaml
    kubectl delete -f mysql-service.yaml
    kubectl delete -f mysql-deployment.yaml
    kubectl delete pvc mysql-pv-claim # Delete the PersistentVolumeClaim
```

If you started Minikube, you can stop it with:

```bash
    minikube stop
```
Or delete the entire Minikube cluster:
```bash
    minikube delete
```
## ⚠️ Important Notes
- **SQL Injection Vulnerability (Addressed)**: The original `backend/index.php` was vulnerable to SQL Injection due to direct string concatenation. The provided `index.php` in this solution has been updated to use `PreparedStatement` to mitigate this risk, which is a crucial security best practice.
- **CORS**: The PHP backend includes `Access-Control-Allow-Origin: *` headers to allow cross-origin requests from the frontend. In a production environment, you should restrict `*` to specific trusted origins.
- **Random ID**: The `id` generation in `index.php` uses `rand()`. For a robust production system, consider using auto-increment IDs in MySQL or UUIDs generated by the application to ensure uniqueness and avoid collisions.
- **Database Credentials**: The MySQL root password (`Senha123`) is hardcoded in `conexao.php` and `mysql-deployment.yaml`. For production, use Kubernetes Secrets to manage sensitive information securely.