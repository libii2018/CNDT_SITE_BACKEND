import { router } from "@inertiajs/react";
import { Head } from "@inertiajs/react";
import { LoginPage } from "@/components/LoginPage";
import { setSession, type ApiUser } from "@/lib/api";

// Connexion — page Inertia /admin/login
export default function Login() {
  const connecte = (token: string, user: ApiUser) => {
    setSession(token, user);
    router.visit("/admin", { replace: true });
  };

  return (
    <>
      <Head title="Connexion" />
      <LoginPage onLogin={connecte} />
    </>
  );
}
