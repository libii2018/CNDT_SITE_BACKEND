import { useEffect, useState, type ReactNode } from "react";
import { ExternalLink, LogOut, Newspaper, BookOpen, Images, Users,
         Mail, Network, LayoutDashboard, Home } from "lucide-react";
import { Link, router, usePage } from "@inertiajs/react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { SECTIONS } from "@/lib/entities";
import { api, clearSession, getToken, type ApiUser } from "@/lib/api";
import { cn } from "@/lib/utils";

// Layout persistant Inertia du dashboard : sidebar par sections (une page
// Laravel par section, navigation client), garde d'authentification par
// token Sanctum (sessionStorage) et badge des messages non lus.

const ICONS: Record<string, ReactNode> = {
  overview: <LayoutDashboard className="h-4 w-4" />,
  articles: <Newspaper className="h-4 w-4" />,
  publications: <BookOpen className="h-4 w-4" />,
  albums: <Images className="h-4 w-4" />,
  members: <Users className="h-4 w-4" />,
  messages: <Mail className="h-4 w-4" />,
  hero: <Home className="h-4 w-4" />,
};

const HREFS: Record<string, string> = {
  overview: "/admin",
  articles: "/admin/articles",
  publications: "/admin/publications",
  albums: "/admin/albums",
  members: "/admin/members",
  messages: "/admin/messages",
  hero: "/admin/hero",
};

export function Layout({ children }: { children: ReactNode }) {
  const { url } = usePage();
  const [open, setOpen] = useState(false);
  const [user, setUser] = useState<ApiUser | null>(null);
  const [unread, setUnread] = useState(0);
  const [pret, setPret] = useState(false);

  const sectionActive = (key: string) =>
    key === "overview" ? url === "/admin" : url.startsWith(HREFS[key]);

  // Garde : sans token → page de connexion
  useEffect(() => {
    if (!getToken()) {
      router.visit("/admin/login", { replace: true });
      return;
    }
    api<ApiUser>("/auth/me")
      .then(setUser)
      .catch(() => clearSession())
      .finally(() => setPret(true));
  }, []);

  // Badge de messages non lus : rafraîchi à chaque navigation et à la
  // demande des pages qui modifient les messages (événement window).
  useEffect(() => {
    const maj = () => {
      api<{ meta?: { total?: number }; data: unknown[] }>("/messages?unread=true&per_page=1")
        .then((r) => setUnread(r.meta?.total ?? r.data.length))
        .catch(() => {});
    };
    maj();
    window.addEventListener("cndt:messages", maj);
    return () => window.removeEventListener("cndt:messages", maj);
  }, [url]);

  const logout = async () => {
    try { await api("/auth/logout", { method: "POST" }); } catch { /* token déjà révoqué */ }
    clearSession();
    router.visit("/admin/login", { replace: true });
  };

  const sectionCourante =
    SECTIONS.find((s) => sectionActive(s.key))?.label ?? "Dashboard";

  if (!pret) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-muted/40">
        <p className="text-sm text-muted-foreground">Chargement du tableau de bord…</p>
      </div>
    );
  }

  return (
    <div className="flex min-h-screen bg-muted/40">
      {/* Sidebar */}
      <aside
        className={cn(
          "fixed inset-y-0 left-0 z-40 flex w-60 flex-col bg-[#0d2e20] text-white transition-transform lg:translate-x-0",
          open ? "translate-x-0" : "-translate-x-full",
        )}
      >
        <div className="border-b border-white/15 px-5 py-3">
          <p className="text-[10px] uppercase tracking-[0.14em] text-white/60">
            République du Cameroun
          </p>
          <p className="text-[10px] text-white/40">Paix · Travail · Patrie</p>
        </div>
        <div className="flex items-center gap-2 px-5 py-4 text-base font-bold">
          <Network className="h-5 w-5 text-[#7ac99b]" />
          CNDT Admin
        </div>
        <nav className="flex-1 space-y-1 p-3">
          {SECTIONS.map((s) => (
            <Link
              key={s.key}
              href={HREFS[s.key]}
              onClick={() => setOpen(false)}
              className={cn(
                "flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors",
                sectionActive(s.key)
                  ? "bg-[#1d5a3b] font-semibold text-white"
                  : "text-primary-foreground/80 hover:bg-white/10 hover:text-white",
              )}
            >
              {ICONS[s.key]}
              {s.label}
              {s.key === "messages" && unread > 0 && (
                <Badge className="ml-auto bg-red-500 text-white hover:bg-red-500">{unread}</Badge>
              )}
            </Link>
          ))}
        </nav>
        <div className="space-y-1 border-t border-white/15 p-3">
          <a
            href="/" target="_blank"
            className="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-primary-foreground/80 hover:bg-white/10 hover:text-white"
          >
            <ExternalLink className="h-4 w-4" />
            Voir le site
          </a>
          <button
            onClick={logout}
            className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm text-primary-foreground/80 hover:bg-white/10 hover:text-white"
          >
            <LogOut className="h-4 w-4" />
            Déconnexion
          </button>
        </div>
      </aside>

      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 lg:hidden" onClick={() => setOpen(false)} />
      )}

      {/* Contenu */}
      <div className="flex min-w-0 flex-1 flex-col lg:ml-60">
        <header className="sticky top-0 z-20 flex items-center justify-between border-b-2 border-b-primary/70 bg-background px-6 py-4">
          <div className="flex items-center gap-3">
            <Button
              variant="outline" size="icon" className="lg:hidden"
              onClick={() => setOpen(!open)}
              aria-label="Menu"
            >
              ☰
            </Button>
            <h1 className="text-lg font-semibold">{sectionCourante}</h1>
          </div>
          <span className="text-sm text-muted-foreground">
            {user ? `${user.name} (${user.email})` : ""}
          </span>
        </header>
        <main className="flex-1 p-6">{children}</main>
      </div>
    </div>
  );
}
