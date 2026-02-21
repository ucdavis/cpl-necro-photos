import { Outlet } from "react-router-dom";
import { Header } from "./components/Header";
import { useApiHealth } from "./utils/hooks";

function App() {
  const isApiHealthy = useApiHealth(60000); // check every 60s

  return (
    <>
      {!isApiHealthy && (
        <div className="bg-yellow-200 text-yellow-900 text-sm px-4 py-2 text-center">
          The connection to the CPL Photos service may be stale. Please refresh
          the page to continue. A refresh will also trigger CAS login if needed.
        </div>
      )}
      <Header />
      <Outlet />
    </>
  );
}

export default App;
