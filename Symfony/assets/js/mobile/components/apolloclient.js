import { ApolloClient } from 'apollo-client';
import { HttpLink, createHttpLink } from 'apollo-link-http';
import { InMemoryCache } from 'apollo-cache-inmemory';
import fetch from 'cross-fetch';

let link;

if (typeof window == 'undefined') {

    link =  createHttpLink({
      fetch: fetch,
    });
}
else{
    link = new HttpLink();
}

const client = new ApolloClient({
  link,
  cache: new InMemoryCache(),
});

export default client;
