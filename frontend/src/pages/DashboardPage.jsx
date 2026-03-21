import { Link } from "react-router-dom";

export default function DashboardPage() {
  return (
    <div>
      <h1>Welcome To LinksApp</h1>
      <Link to="/links">Go to Links</Link>
    </div>
  );
}
