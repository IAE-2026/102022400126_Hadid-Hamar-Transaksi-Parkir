<?php

namespace App\Http\Controllers;

use App\Services\TransactionStore;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class GraphqlController extends Controller
{
    public function query(Request $request, TransactionStore $store): JsonResponse
    {
        $query = $request->input('query');

        if (! is_string($query) || trim($query) === '') {
            return response()->json([
                'errors' => [
                    ['message' => 'Query GraphQL wajib diisi.'],
                ],
            ], 400);
        }

        try {
            $result = GraphQL::executeQuery(
                $this->schema($store),
                $query,
                null,
                null,
                $request->input('variables', [])
            );

            return response()->json($result->toArray());
        } catch (Throwable $exception) {
            return response()->json([
                'errors' => [
                    ['message' => $exception->getMessage()],
                ],
            ], 500);
        }
    }

    public function playground(): Response
    {
        return response($this->playgroundHtml())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function schema(TransactionStore $store): Schema
    {
        $transactionType = new ObjectType([
            'name' => 'Transaction',
            'fields' => [
                'id' => Type::string(),
                'location_id' => Type::string(),
                'member_card_id' => Type::string(),
                'entry_time' => Type::string(),
                'exit_time' => Type::string(),
                'duration_hours' => Type::float(),
                'base_rate' => Type::float(),
                'benefit' => Type::float(),
                'total_amount' => Type::float(),
                'status' => Type::string(),
                'payment_method' => Type::string(),
                'voucher_code' => Type::string(),
                'created_at' => Type::string(),
            ],
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'transactions' => [
                    'type' => Type::listOf($transactionType),
                    'resolve' => fn () => $store->all(),
                ],
                'transaction' => [
                    'type' => $transactionType,
                    'args' => [
                        'id' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => fn ($root, array $args) => $store->find((string) $args['id']),
                ],
            ],
        ]);

        return new Schema(['query' => $queryType]);
    }

    private function playgroundHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GraphQL - Service B Transaksi & Pembayaran</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.css">
  <style>
    body { margin: 0; height: 100vh; display: flex; flex-direction: column; }
    #auth { display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: #161616; color: #fff; font-family: monospace; }
    #auth input { width: 220px; padding: 7px 10px; border: 1px solid #555; border-radius: 4px; background: #252525; color: #9cff9c; }
    #graphiql { flex: 1; min-height: 0; }
  </style>
</head>
<body>
  <div id="auth">
    <label for="iae-key">X-IAE-KEY</label>
    <input id="iae-key" value="102022400126">
  </div>
  <div id="graphiql"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.js"></script>
  <script>
    function graphQLFetcher(params) {
      return fetch('/graphql', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-IAE-KEY': document.getElementById('iae-key').value.trim()
        },
        body: JSON.stringify(params)
      }).then(function (response) { return response.json(); });
    }

    ReactDOM.render(
      React.createElement(GraphiQL, {
        fetcher: graphQLFetcher,
        defaultQuery: '# Service B - Transaksi & Pembayaran\n# Query daftar transaksi parkir\n\n{\n  transactions {\n    id\n    location_id\n    duration_hours\n    total_amount\n    status\n  }\n}'
      }),
      document.getElementById('graphiql')
    );
  </script>
</body>
</html>
HTML;
    }
}
