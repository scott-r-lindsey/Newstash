import React from "react";
import Home from "../components/Home";
import Work from "../components/Work";
import { Route } from "react-router-dom";

const BooksApp = ({ initialProps, appContext }) => {
  return (
    <div>
      <Route
        path={"/book/:id/:slug"}
        render={props => (
          <Work {...initialProps} base={appContext.base} {...props} />
        )}
      />
      <Route
        path={"/"}
        exact
        render={props => (
          <Home {...initialProps} base={appContext.base} {...props} />
        )}
      />
    </div>
  );
};

export default BooksApp;
